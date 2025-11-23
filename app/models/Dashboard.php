<?php
class Dashboard {
  private $pdo;
  public function __construct($pdo){ $this->pdo=$pdo; }

  public function kpis(){
    $low = $this->pdo->query("SELECT COUNT(*) c FROM item_lots WHERE qty_on_hand <= 0")->fetch()['c'] ?? 0;
    $near = $this->pdo->query("SELECT COUNT(*) c FROM item_lots WHERE expiry_date IS NOT NULL AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 60 DAY)")->fetch()['c'] ?? 0;
    $in_transit = $this->pdo->query("SELECT COUNT(*) c FROM shipments WHERE status IN ('IN_TRANSIT','OUT_FOR_DELIVERY','DELAYED')")->fetch()['c'] ?? 0;
    return ['low_stock'=>$low,'near_expiry'=>$near,'in_transit'=>$in_transit];
  }

  public function recentPOs(){
    $sql = "SELECT p.id,p.po_no,p.status,p.expected_date,s.name as supplier
            FROM purchase_orders p
            JOIN suppliers s ON s.id=p.supplier_id
            ORDER BY p.id DESC LIMIT 5";
    return $this->pdo->query($sql)->fetchAll();
  }

  public function latestShipments(){
    $sql = "SELECT sh.id,sh.carrier,sh.status,sh.eta,p.po_no
            FROM shipments sh JOIN purchase_orders p ON p.id=sh.po_id
            ORDER BY sh.id DESC LIMIT 5";
    return $this->pdo->query($sql)->fetchAll();
  }
}
