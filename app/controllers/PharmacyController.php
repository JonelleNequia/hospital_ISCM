<?php
require_once __DIR__ . '/../models/ItemModel.php';

class PharmacyController {
  private $app;
  private $db;
  public function __construct($app) {
    $this->app = $app;
    $this->db = $app->db;
  }

  // Endpoint for Pharmacy system to report dispensed items and decrement stock
  public function updateStock() {
    header('Content-Type: application/json');

    // Optional shared API key check via environment
    $expected = getenv('PHARMACY_API_KEY') ?: '';
    if ($expected !== '') {
      $provided = $_SERVER['HTTP_X_API_KEY'] ?? '';
      if (!hash_equals($expected, $provided)) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
        return;
      }
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      echo json_encode(['ok' => false, 'error' => 'Method Not Allowed']);
      return;
    }

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data) || empty($data['items'])) {
      http_response_code(400);
      echo json_encode(['ok' => false, 'error' => 'Invalid payload, expected { items: [{sku,qty}] }']);
      return;
    }

    try {
      $this->db->beginTransaction();
      // default location = PHARM
      $stLoc = $this->db->prepare('SELECT id FROM locations WHERE code = ? LIMIT 1');
      $stLoc->execute(['PHARM']);
      $loc = $stLoc->fetchColumn() ?: null;

      $results = [];
      foreach ($data['items'] as $it) {
        $sku = trim($it['sku'] ?? '');
        $qty = (int)($it['qty'] ?? 0);
        if ($sku === '' || $qty <= 0) { continue; }

        // find item id
        $stItem = $this->db->prepare('SELECT id FROM items WHERE sku = ? LIMIT 1');
        $stItem->execute([$sku]);
        $itemId = $stItem->fetchColumn();
        if (!$itemId) {
          $results[] = ['sku'=>$sku,'ok'=>false,'error'=>'unknown_sku'];
          continue;
        }

        $remaining = $qty;
        // select lots with qty_on_hand > 0
        $stLots = $this->db->prepare('SELECT id, qty_on_hand FROM item_lots WHERE item_id = ? AND location_id = ? AND qty_on_hand>0 ORDER BY expiry_date IS NULL, expiry_date ASC, id ASC');
        $stLots->execute([$itemId, $loc]);
        $lots = $stLots->fetchAll();
        foreach ($lots as $lot) {
          if ($remaining <= 0) break;
          $take = min($remaining, (int)$lot['qty_on_hand']);
          $upd = $this->db->prepare('UPDATE item_lots SET qty_on_hand = qty_on_hand - ? WHERE id = ? AND qty_on_hand >= ?');
          $upd->execute([$take, $lot['id'], $take]);
          $mv = $this->db->prepare('INSERT INTO inventory_movements (item_id, lot_id, movement_type, qty, from_location_id, to_location_id, ref_type, ref_id, moved_by) VALUES (?,?,?,?,?,?,?,?,?)');
          $mv->execute([$itemId, $lot['id'], 'PHARM_DISPENSE', $take, $loc, null, 'PHARMACY', null, null]);
          $remaining -= $take;
        }

        if ($remaining > 0) {
          // not enough stock, rollback
          $this->db->rollBack();
          http_response_code(409);
          echo json_encode(['ok'=>false,'error'=>'insufficient_stock','sku'=>$sku,'needed'=>$qty,'available'=>$qty-$remaining]);
          return;
        }

        $results[] = ['sku'=>$sku,'ok'=>true,'qty'=>$qty];
      }

      $this->db->commit();
      echo json_encode(['ok'=>true,'results'=>$results]);
      return;
    } catch (Throwable $e) {
      if ($this->db->inTransaction()) $this->db->rollBack();
      error_log('PharmacyController updateStock: '.$e->getMessage());
      http_response_code(500);
      echo json_encode(['ok'=>false,'error'=>'server_error']);
      return;
    }
  }
}
