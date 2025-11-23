<?php
class ListModel {
  private $pdo;
  public function __construct($pdo){ $this->pdo=$pdo; }

  public function items($q=''){
    $sql = "SELECT id,sku,name,category,uom,reorder_point FROM items";
    if ($q) { $sql .= " WHERE name LIKE :q OR sku LIKE :q"; }
    $st = $this->pdo->prepare($sql);
    if ($q) { $st->bindValue(':q','%'.$q.'%'); }
    $st->execute();
    return $st->fetchAll();
  }

  public function pos($q=''){
    $sql = "SELECT p.po_no,s.name as supplier,p.status,p.expected_date
            FROM purchase_orders p JOIN suppliers s ON s.id=p.supplier_id";
    if ($q) { $sql .= " WHERE p.po_no LIKE :q"; }
    $st = $this->pdo->prepare($sql);
    if ($q) { $st->bindValue(':q','%'.$q.'%'); }
    $st->execute();
    return $st->fetchAll();
  }

  public function shipments($q=''){
    $sql = "SELECT sh.id, p.po_no, sh.carrier, sh.tracking_no, sh.status, sh.eta
            FROM shipments sh JOIN purchase_orders p ON p.id=sh.po_id
            ORDER BY sh.id DESC";
    return $this->pdo->query($sql)->fetchAll();
  }

  public function grn($q=''){
      $sql = "SELECT g.id, g.grn_no, p.po_no, g.received_at, g.status
        FROM goods_receipts g JOIN purchase_orders p ON p.id=g.po_id
        ORDER BY g.id DESC";
    return $this->pdo->query($sql)->fetchAll();
  }

  public function inventory($q=''){
    $sql = "SELECT i.sku,i.name,l.lot_no,l.expiry_date,loc.name as location,l.qty_on_hand
            FROM item_lots l
            JOIN items i ON i.id=l.item_id
            JOIN locations loc ON loc.id=l.location_id
            ORDER BY i.name";
    return $this->pdo->query($sql)->fetchAll();
  }

  public function requisitions($q=''){
    $sql = "SELECT r.req_no,d.name as department,r.status,r.priority
            FROM requisitions r JOIN departments d ON d.id=r.department_id";
    $params = [];
    if ($q !== '') {
      $sql .= " WHERE r.req_no LIKE :q OR d.name LIKE :q";
      $params[':q'] = '%'.$q.'%';
    }
    $sql .= " ORDER BY r.id DESC";
    $st = $this->pdo->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
  }
}
