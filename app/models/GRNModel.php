<?php
class GRNModel {
  private $pdo;
  public function __construct($pdo){ $this->pdo=$pdo; }

  public function createGRN($po_id,$received_by,$location_id,$remarks){
    $grn_no = $this->nextGRN();
    $st=$this->pdo->prepare("INSERT INTO goods_receipts (grn_no,po_id,received_by,status,remarks) VALUES (?,?,?,'POSTED',?)");
    $st->execute([$grn_no,$po_id,$received_by,$remarks]);
    return (int)$this->pdo->lastInsertId();
  }

  public function addLine($grn_id,$po_item_id,$item_id,$lot_no,$expiry,$qty,$location_id){
    // Insert GRN line
    $st = $this->pdo->prepare("INSERT INTO goods_receipt_items (grn_id,po_item_id,item_id,lot_no,expiry_date,qty_received,location_id)
                               VALUES (?,?,?,?,?,?,?)");
    $st->execute([$grn_id,$po_item_id,$item_id,$lot_no, $expiry ?: null, $qty, $location_id]);

    // Update PO item received quantity
    $this->pdo->prepare("UPDATE po_items SET qty_received = qty_received + ? WHERE id=?")
              ->execute([$qty, $po_item_id]);

    // Upsert lot at location
    $st = $this->pdo->prepare("SELECT id FROM item_lots WHERE item_id=? AND lot_no=? AND location_id=?");
    $st->execute([$item_id, $lot_no, $location_id]);
    $lot = $st->fetch(PDO::FETCH_ASSOC);
    if ($lot) {
      $lot_id = (int)$lot['id'];
      $this->pdo->prepare("UPDATE item_lots SET qty_on_hand = qty_on_hand + ?, expiry_date = COALESCE(?, expiry_date) WHERE id=?")
                ->execute([$qty, $expiry ?: null, $lot_id]);
    } else {
      $this->pdo->prepare("INSERT INTO item_lots (item_id, lot_no, expiry_date, location_id, qty_on_hand)
                           VALUES (?,?,?,?,?)")
                ->execute([$item_id, $lot_no, $expiry ?: null, $location_id, $qty]);
      $lot_id = (int)$this->pdo->lastInsertId();
    }

    // Record inventory movement (IN)
    $this->pdo->prepare("INSERT INTO inventory_movements (item_id, lot_id, movement_type, qty, to_location_id, ref_type, ref_id, moved_by)
                         VALUES (?,?,?,?,?,'GRN',?,?)")
              ->execute([$item_id, $lot_id ?? null, 'IN', $qty, $location_id, $grn_id, $_SESSION['user']['id'] ?? 1]);
  }

  private function nextGRN(){
    $n = $this->pdo->query("SELECT LPAD(COALESCE(MAX(id)+1,1),6,'0') n FROM goods_receipts")->fetch()['n'];
    return 'GRN-' . $n;
  }
}