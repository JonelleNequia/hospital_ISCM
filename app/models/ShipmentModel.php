<?php
class ShipmentModel {
  private PDO $db;
  public function __construct(PDO $db){ $this->db=$db; }

  public function listPOs(){
    return $this->db->query("SELECT id, po_no, status FROM purchase_orders ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
  }
  public function listShipments(){
    $sql="SELECT s.id,s.po_id,p.po_no,s.carrier,s.tracking_no,s.status,s.eta,s.shipped_at,s.delivered_at
          FROM shipments s JOIN purchase_orders p ON p.id=s.po_id ORDER BY s.id DESC";
    return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
  }
  public function createShipment(array $d,int $user){
    $po=(int)($d['po_id']??0);
    $carrier=trim($d['carrier']??'');
    $tracking=trim($d['tracking_no']??'');
    $eta=$d['eta']??null;
    if($po<=0) throw new \Exception('PO required');
    $st=$this->db->prepare("INSERT INTO shipments(po_id,carrier,tracking_no,status,eta,shipped_at) VALUES (?,?,?,?,?,NOW())");
    $st->execute([$po,$carrier,$tracking,'IN_TRANSIT',$eta]);
  }
  public function getShipment(int $id){
    $st=$this->db->prepare("SELECT s.*,p.po_no FROM shipments s JOIN purchase_orders p ON p.id=s.po_id WHERE s.id=?");
    $st->execute([$id]); return $st->fetch(PDO::FETCH_ASSOC)?:null;
  }
  public function events(int $sid){
    $st=$this->db->prepare("SELECT * FROM shipment_events WHERE shipment_id=? ORDER BY event_time ASC, id ASC");
    $st->execute([$sid]); return $st->fetchAll(PDO::FETCH_ASSOC);
  }
  public function addEvent(array $d){
    $sid=(int)($d['shipment_id']??0);
    $status=trim($d['status']??'');
    $remarks=trim($d['remarks']??'');
    $time=$d['event_time']??date('Y-m-d H:i:s');
    if($sid<=0||$status==='') throw new \Exception('Shipment & status required');

    $this->db->beginTransaction();
    try{
      $ins=$this->db->prepare("INSERT INTO shipment_events (shipment_id,status,event_time,remarks) VALUES (?,?,?,?)");
      $ins->execute([$sid,$status,$time,$remarks]);

      // mirror status to shipment header
      $u=$this->db->prepare("UPDATE shipments SET status=?,
                  delivered_at = CASE WHEN ?='DELIVERED' THEN NOW() ELSE delivered_at END
                WHERE id=?");
      $u->execute([$status,$status,$sid]);

      $this->db->commit();
    }catch(\Throwable $e){ $this->db->rollBack(); throw $e; }
  }
}
