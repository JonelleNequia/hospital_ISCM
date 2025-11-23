<?php
class POModel {
  private $pdo;
  public function __construct($pdo){ $this->pdo=$pdo; }

  public function createPO(array $d, int $created_by){
    $po_no = $this->nextPONumber();
    $supplier_id = (int)($d['supplier_id'] ?? 0);
    $expected_date = !empty($d['expected_date']) ? $d['expected_date'] : null;
    $notes = isset($d['notes']) ? trim($d['notes']) : '';
    $st = $this->pdo->prepare("INSERT INTO purchase_orders (po_no,supplier_id,status,expected_date,notes,created_by)
                               VALUES (?,?, 'SUBMITTED',?,?,?)");
    $st->execute([$po_no,$supplier_id,$expected_date,$notes,$created_by]);
    return (int)$this->pdo->lastInsertId();
  }

  public function addLine($po_id,$item_id,$qty,$price){
    $st=$this->pdo->prepare("INSERT INTO po_items (po_id,item_id,qty_ordered,unit_price) VALUES (?,?,?,?)");
    $st->execute([$po_id,$item_id,$qty,$price]);
  }

  public function find($po_id){
    $po = $this->pdo->prepare("SELECT p.*, s.name supplier FROM purchase_orders p JOIN suppliers s ON s.id=p.supplier_id WHERE p.id=?");
    $po->execute([$po_id]); $header = $po->fetch();
    $lines = $this->pdo->prepare("SELECT pi.*, i.sku, i.name
                                  FROM po_items pi JOIN items i ON i.id=pi.item_id
                                  WHERE pi.po_id=?");
    $lines->execute([$po_id]);
    return ['header'=>$header,'lines'=>$lines->fetchAll()];
  }
  public function supplierOptions(): array {
    $sql = "SELECT id, name FROM suppliers ORDER BY name";
    return $this->pdo->query($sql)->fetchAll();
  }


  public function itemOptions(): array {
    $sql = "SELECT id, sku, name FROM items WHERE active=1 ORDER BY name";
    return $this->pdo->query($sql)->fetchAll();
  }


  public function listPOs(string $q = ''): array {
    $sql = "SELECT p.id, p.po_no, s.name AS supplier, p.status, p.expected_date
            FROM purchase_orders p
            JOIN suppliers s ON s.id = p.supplier_id";
    $args = [];
    if ($q !== '') {
      $sql .= " WHERE p.po_no LIKE ? OR s.name LIKE ?";
      $like = "%$q%";
      $args = [$like, $like];
    }
    $sql .= " ORDER BY p.id DESC";
    $st = $this->pdo->prepare($sql);
    $st->execute($args);
    return $st->fetchAll();
  }
  private function nextPONumber(){
    $n = $this->pdo->query("SELECT LPAD(COALESCE(MAX(id)+1,1),6,'0') n FROM purchase_orders")->fetch()['n'];
    return 'PO-' . $n;
  }
}
