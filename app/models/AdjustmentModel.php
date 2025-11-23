<?php
class AdjustmentModel {
  private PDO $db;
  public function __construct(PDO $db){ $this->db=$db; }
  public function adjust(array $d,int $user){
    $lot =(int)($d['lot_id']??0);
    $delta=(int)($d['delta']??0); // ± qty
    $reason=trim($d['reason']??'ADJUST');
    if($lot<=0||$delta===0) throw new \Exception('Lot and non-zero delta required');

    $this->db->beginTransaction();
    try{
      $q=$this->db->prepare("SELECT id,item_id,location_id,qty_on_hand FROM item_lots WHERE id=? FOR UPDATE");
      $q->execute([$lot]); $L=$q->fetch(PDO::FETCH_ASSOC);
      if(!$L) throw new \Exception('Lot not found');
      $new=(int)$L['qty_on_hand']+$delta;
      if($new<0) throw new \Exception('Resulting stock cannot be negative');

      $u=$this->db->prepare("UPDATE item_lots SET qty_on_hand=? WHERE id=?");
      $u->execute([$new,$lot]);

      $mv=$this->db->prepare("INSERT INTO inventory_movements(item_id,lot_id,movement_type,qty,from_location_id,to_location_id,ref_type,ref_id,moved_by)
                              VALUES (?,?,?,?,?,?,?,?,?)");
      $type='ADJUSTMENT';
      $qty=abs($delta);
      if($delta>0){
        $mv->execute([(int)$L['item_id'],$L['id'],$type,$qty,null,(int)$L['location_id'],$reason,null,$user]);
      }else{
        $mv->execute([(int)$L['item_id'],$L['id'],$type,$qty,(int)$L['location_id'],null,$reason,null,$user]);
      }

      $this->db->commit();
    }catch(\Throwable $e){ $this->db->rollBack(); throw $e; }
  }
}
