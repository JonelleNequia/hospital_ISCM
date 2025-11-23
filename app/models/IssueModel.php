<?php
class IssueModel {
  private PDO $db;
  public function __construct(PDO $db){ $this->db=$db; }

  public function listIssues(){
    $sql="SELECT i.id,i.issue_no,i.requisition_id,i.issued_at,u.name issued_by
          FROM issues i JOIN users u ON u.id=i.issued_by ORDER BY i.id DESC";
    return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
  }
  public function locations(){
    return $this->db->query("SELECT id,code,name FROM locations ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
  }
  public function openRequisitions(){
    $sql="SELECT r.id,r.req_no,d.name dept,r.status
          FROM requisitions r JOIN departments d ON d.id=r.department_id
          WHERE r.status IN ('SUBMITTED','APPROVED','PARTIALLY_ISSUED')
          ORDER BY r.id DESC";
    return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
  }
  public function getRequisition(int $id){
    $st=$this->db->prepare("SELECT r.*,d.name dept_name,d.code dept_code
                            FROM requisitions r JOIN departments d ON d.id=r.department_id
                            WHERE r.id=?");
    $st->execute([$id]); return $st->fetch(PDO::FETCH_ASSOC)?:null;
  }
  public function getRequisitionLines(int $req_id){
    $st=$this->db->prepare("SELECT ri.id,ri.item_id,i.name item_name,i.uom,
                                   ri.qty_requested,ri.qty_issued
                            FROM requisition_items ri JOIN items i ON i.id=ri.item_id
                            WHERE ri.requisition_id=?");
    $st->execute([$req_id]); return $st->fetchAll(PDO::FETCH_ASSOC);
  }
  private function lotsForItemAtLocation(int $item,int $loc){
    $st=$this->db->prepare("SELECT id,lot_no,expiry_date,qty_on_hand
                            FROM item_lots
                            WHERE item_id=? AND location_id=? AND qty_on_hand>0
                            ORDER BY (expiry_date IS NULL), expiry_date ASC, id ASC");
    $st->execute([$item,$loc]); return $st->fetchAll(PDO::FETCH_ASSOC);
  }
  private function nextIssueNo():string{
    $no=$this->db->query("SELECT CONCAT('ISS-',LPAD(COALESCE(MAX(id)+1,1),6,'0')) v FROM issues")->fetchColumn();
    return $no?:'ISS-000001';
  }

  public function createIssue(array $data,int $userId):int{
    $req=(int)($data['requisition_id']??0);
    $loc=(int)($data['location_id']??0);
    $items=$data['issue']['item_id']??[];
    $lots =$data['issue']['lot_id'] ??[];
    $qtys =$data['issue']['qty']    ??[];
    if($req<=0||$loc<=0) throw new \Exception('Requisition/Location required');

    $this->db->beginTransaction();
    try{
      // lock lines
      $lock=$this->db->prepare("SELECT id,item_id,qty_requested,qty_issued
                                FROM requisition_items WHERE requisition_id=? FOR UPDATE");
      $lock->execute([$req]); $map=[];
      foreach($lock->fetchAll(PDO::FETCH_ASSOC) as $r){ $map[(int)$r['item_id']]=$r; }

      // header
      $no=$this->nextIssueNo();
      $st=$this->db->prepare("INSERT INTO issues(issue_no,requisition_id,issued_by) VALUES (?,?,?)");
      $st->execute([$no,$req,$userId]); $issue=(int)$this->db->lastInsertId();

      $insItem=$this->db->prepare("INSERT INTO issue_items(issue_id,item_id,lot_id,qty_issued) VALUES (?,?,?,?)");
      $decLot =$this->db->prepare("UPDATE item_lots SET qty_on_hand=qty_on_hand-? WHERE id=? AND qty_on_hand>=?");
      $mv     =$this->db->prepare("INSERT INTO inventory_movements(item_id,lot_id,movement_type,qty,from_location_id,to_location_id,ref_type,ref_id,moved_by)
                                   VALUES (?,?,?,?,?,?,?,?,?)");
      $upReq  =$this->db->prepare("UPDATE requisition_items SET qty_issued=qty_issued+? WHERE requisition_id=? AND item_id=?");

      $n=min(count($items),count($lots),count($qtys));
      for($i=0;$i<$n;$i++){
        $item=(int)$items[$i]; $lot=(int)$lots[$i]; $qty=max(0,(int)$qtys[$i]);
        if($item<=0||$qty<=0) continue;
        if(!isset($map[$item])) throw new \Exception("Item $item not in requisition");
        $rem=(int)$map[$item]['qty_requested']-(int)$map[$item]['qty_issued'];
        if($qty>$rem) throw new \Exception("Qty exceeds remaining requested");

        // lock lot row
        $lotRow=$this->db->prepare("SELECT id,item_id,location_id,qty_on_hand FROM item_lots WHERE id=? FOR UPDATE");
        $lotRow->execute([$lot]); $L=$lotRow->fetch(PDO::FETCH_ASSOC);
        if(!$L || (int)$L['item_id']!==$item || (int)$L['location_id']!==$loc) throw new \Exception("Invalid lot selection");
        if((int)$L['qty_on_hand']<$qty) throw new \Exception("Insufficient lot stock");

        $insItem->execute([$issue,$item,$lot,$qty]);
        $decLot->execute([$qty,$lot,$qty]); if($decLot->rowCount()===0) throw new \Exception("Concurrent stock change");
        $mv->execute([$item,$lot,'OUT',$qty,$loc,null,'ISSUE',$issue,$userId]);
        $upReq->execute([$qty,$req,$item]);
        $map[$item]['qty_issued']+=(int)$qty;
      }

      // update requisition status
      $rows=$this->db->prepare("SELECT qty_requested,qty_issued FROM requisition_items WHERE requisition_id=?");
      $rows->execute([$req]); $all=$rows->fetchAll(PDO::FETCH_ASSOC);
      $full=true; $any=false;
      foreach($all as $r){ $any=$any||((int)$r['qty_issued']>0); if((int)$r['qty_issued']<(int)$r['qty_requested']) $full=false; }
      $status=$full?'FULFILLED':($any?'PARTIALLY_ISSUED':'SUBMITTED');
      $this->db->prepare("UPDATE requisitions SET status=? WHERE id=?")->execute([$status,$req]);

      $this->db->commit(); return $issue;
    }catch(\Throwable $e){ $this->db->rollBack(); throw $e; }
  }
}
