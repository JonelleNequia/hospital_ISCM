<?php
class TransferModel {
  private PDO $db;
  public function __construct(PDO $db){ $this->db=$db; }
  public function locations(){
    return $this->db->query("SELECT id,code,name FROM locations ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
  }
  public function transfer(array $d,int $user){
    $from=(int)($d['from_location_id']??0);
    $to  =(int)($d['to_location_id']??0);
    $lot =(int)($d['lot_id']??0);
    $qty =max(0,(int)($d['qty']??0));
    if($from<=0||$to<=0||$from===$to) throw new \Exception('From/To required and must differ');
    if($lot<=0||$qty<=0) throw new \Exception('Lot/Qty required');

    $this->db->beginTransaction();
    try{
      // lock lot row
      $q=$this->db->prepare("SELECT id,item_id,location_id,qty_on_hand,lot_no,expiry_date FROM item_lots WHERE id=? FOR UPDATE");
      $q->execute([$lot]); $L=$q->fetch(PDO::FETCH_ASSOC);
      if(!$L || (int)$L['location_id']!==$from) throw new \Exception('Invalid lot / from location');
      if((int)$L['qty_on_hand']<$qty) throw new \Exception('Insufficient stock');

      // decrement source
      $dec=$this->db->prepare("UPDATE item_lots SET qty_on_hand=qty_on_hand-? WHERE id=? AND qty_on_hand>=?");
      $dec->execute([$qty,$lot,$qty]); if($dec->rowCount()===0) throw new \Exception('Concurrent change');

      // upsert destination lot (same item & lot_no)
      $ins=$this->db->prepare("INSERT INTO item_lots(item_id,lot_no,expiry_date,location_id,qty_on_hand)
                               VALUES (?,?,?,?,?)
                               ON DUPLICATE KEY UPDATE qty_on_hand=qty_on_hand+VALUES(qty_on_hand)");
      $ins->execute([(int)$L['item_id'],$L['lot_no'],$L['expiry_date'],$to,$qty]);

      // movements OUT and IN
      $mv=$this->db->prepare("INSERT INTO inventory_movements(item_id,lot_id,movement_type,qty,from_location_id,to_location_id,ref_type,ref_id,moved_by)
                              VALUES (?,?,?,?,?,?,?,?,?)");
      $mv->execute([(int)$L['item_id'],$L['id'],'TRANSFER',$qty,$from,$to,'TRANSFER',null,$user]);

      $this->db->commit();
    }catch(\Throwable $e){ $this->db->rollBack(); throw $e; }
  }
}
