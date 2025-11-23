<?php
require_once __DIR__ . '/../models/GRNModel.php';
// Optional billing service - used to forward GRNs to external billing systems when configured
require_once __DIR__ . '/../services/BillingService.php';

class GRNController {
  private $app;
  private $m;

  public function __construct($app){
    $this->app = $app;
    $this->m   = new GRNModel($app->db);
  }

  public function create(){
    $this->app->requireLogin();

    $po_id = (int)($_GET['po_id'] ?? 0);
    $po    = null;
    $lines = [];

    if ($po_id > 0) {
      $st = $this->app->db->prepare("SELECT id, po_no FROM purchase_orders WHERE id = ?");
      $st->execute([$po_id]);
      $po = $st->fetch();

      $st = $this->app->db->prepare("
        SELECT pi.id, pi.item_id, pi.qty_ordered, pi.qty_received,
               i.sku, i.name
        FROM po_items pi
        JOIN items i ON i.id = pi.item_id
        WHERE pi.po_id = ?
        ORDER BY pi.id ASC
      ");
      $st->execute([$po_id]);
      $lines = $st->fetchAll();
    }

    $pos = $this->app->db->query("SELECT id, po_no FROM purchase_orders ORDER BY id DESC LIMIT 50")->fetchAll();
    $loc = $this->app->db->query("SELECT id, name FROM locations ORDER BY name")->fetchAll();

    $this->app->view('grn/create', compact('po','pos','loc','lines'));
  }

  public function store(){
    $this->app->requireLogin();
    $this->app->requireCsrf();

    $pdo = $this->app->db;
    $pdo->beginTransaction();

    try {
      $po_id = (int)($_POST['po_id'] ?? 0);
      if ($po_id <= 0) { throw new Exception('PO is required'); }

      $location_id = (int)($_POST['location_id'] ?? 0);
      if ($location_id <= 0) { throw new Exception('Location is required'); }

      $lines = $_POST['lines'] ?? [];
      // Fetch item flags for expiry validation (cache per item_id)

/* MINIMAL AUTOFILL */
// If user didn't post any lines or all are zero, but PO has outstanding, default to outstanding
$allZero = true;
foreach (($lines ?: []) as $__t) { if ((int)($__t['qty_received'] ?? 0) > 0) { $allZero = false; break; } }
if (empty($lines) || $allZero) {
  $st = $pdo->prepare("SELECT id AS po_item_id, item_id, qty_ordered, qty_received FROM po_items WHERE po_id = ?");
  $st->execute([$po_id]);
  $auto = [];
  while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
    $out = max(0, (int)$r['qty_ordered'] - (int)$r['qty_received']);
    if ($out > 0) {
      $auto[] = [
        'po_item_id'   => (int)$r['po_item_id'],
        'item_id'      => (int)$r['item_id'],
        'qty_received' => $out,
        'lot_no'       => '',
        'expiry_date'  => null,
      ];
    }
  }
  if (!empty($auto)) { $lines = $auto; }
}


/* AUTOFILL OUTSTANDING START */
// Load PO items to compute outstanding per line
$poItems = $pdo->prepare("SELECT id AS po_item_id, item_id, qty_ordered, qty_received FROM po_items WHERE po_id = ?");
$poItems->execute([$po_id]);
$poOutstanding = []; // po_item_id => ['item_id'=>..., 'out'=>...]
while ($r = $poItems->fetch(PDO::FETCH_ASSOC)) {
  $out = max(0, (int)$r['qty_ordered'] - (int)$r['qty_received']);
  $poOutstanding[(int)$r['po_item_id']] = ['item_id' => (int)$r['item_id'], 'out' => $out];
}

// Normalize posted lines; auto-default qty to outstanding if empty/zero
$normLines = [];
foreach (($lines ?: []) as $l) {
  $pid = (int)($l['po_item_id'] ?? 0);
  if ($pid <= 0 || !isset($poOutstanding[$pid])) continue;
  $out = (int)$poOutstanding[$pid]['out'];
  if ($out <= 0) continue;
  $posted = (int)($l['qty_received'] ?? 0);
  $qty = $posted > 0 ? $posted : $out; // default to outstanding when empty/zero
  $qty = max(0, min($qty, $out));     // clamp to outstanding
  $normLines[] = [
    'po_item_id'  => $pid,
    'item_id'     => (int)($l['item_id'] ?? $poOutstanding[$pid]['item_id'] ?? 0),
    'qty_received'=> $qty,
    'lot_no'      => trim((string)($l['lot_no'] ?? '')),
    'expiry_date' => ($l['expiry_date'] ?? null),
  ];
}

// If no lines were posted (or all invalid), but the PO has outstanding, auto-pick all outstanding lines
if (empty($lines) && !empty($poOutstanding)) {
  foreach ($poOutstanding as $pid => $info) {
    if ($info['out'] > 0) {
      $normLines[] = [
        'po_item_id'   => $pid,
        'item_id'      => $info['item_id'],
        'qty_received' => $info['out'],
        'lot_no'       => '',
        'expiry_date'  => null,
      ];
    }
  }
}

// Replace original lines with normalized lines
$lines = $normLines;
/* AUTOFILL OUTSTANDING END */

      // Require at least one normalized line with a positive qty
      $hasQty = false;
      foreach ($lines as $l) { if (((int)($l['qty_received'] ?? 0)) > 0) { $hasQty = true; break; } }
      if (!$hasQty) {
        throw new Exception('No positive quantities to receive');
      }

      $grn_id = $this->m->createGRN(
        $po_id,
        (int)($_SESSION['user']['id'] ?? 0),
        $location_id,
        $_POST['remarks'] ?? null
      );

      foreach ($lines as $l) {
        $qty = (int)($l['qty_received'] ?? 0);
        if ($qty <= 0) continue;

        
// --- Auto-reject expired lots when expiry_required ---
$item_id_for_flag = (int)($l['item_id'] ?? 0);
$expiryRequired = 0;
if ($item_id_for_flag > 0) {
  $qFlag = $pdo->prepare("SELECT expiry_required FROM items WHERE id=?");
  $qFlag->execute([$item_id_for_flag]);
  $expiryRequired = (int)($qFlag->fetchColumn() ?: 0);
}
if ($expiryRequired === 1 && !empty($l['expiry_date'])) {
  $exp = date('Y-m-d', strtotime($l['expiry_date']));
  if ($exp < date('Y-m-d')) {
    $l['qty_rejected'] = ((int)($l['qty_rejected'] ?? 0)) + (int)($l['qty_received'] ?? 0);
    $l['qty_received'] = 0;
    if (empty($l['reject_reason'])) $l['reject_reason'] = 'EXPIRED';
    if (empty($l['reject_disposition'])) $l['reject_disposition'] = 'RETURN_TO_SUPPLIER';
    $qty = 0;
  }
}

// --- Handle rejects (record discrepancy + optional quarantine movement) ---
$rej = (int)($l['qty_rejected'] ?? 0);
$rej_reason = strtoupper(trim((string)($l['reject_reason'] ?? '')));
$rej_disp   = strtoupper(trim((string)($l['reject_disposition'] ?? '')));

if ($rej > 0 && in_array($rej_reason, ['DAMAGED','EXPIRED','SHORT','OVER','WRONG_ITEM','OTHER'], true)) {
  $insD = $pdo->prepare("INSERT INTO grn_discrepancies
    (grn_id, grn_item_id, po_item_id, item_id, qty_rejected, reason, disposition, notes)
    VALUES (?,?,?,?,?,?,?,?)");
  $insD->execute([
    $grn_id,
    null,
    (int)($l['po_item_id'] ?? 0),
    (int)($l['item_id'] ?? 0),
    $rej,
    $rej_reason,
    in_array($rej_disp, ['RETURN_TO_SUPPLIER','DISCARD','QUARANTINE'], true) ? $rej_disp : 'RETURN_TO_SUPPLIER',
    trim((string)($l['reject_notes'] ?? ''))
  ]);

  if ($rej_disp === 'QUARANTINE') {
    static $quarLocId = null;
    if ($quarLocId === null) {
      $q = $pdo->query("SELECT id FROM locations WHERE code='QUAR' LIMIT 1");
      $quarLocId = (int)($q->fetchColumn() ?: 0);
    }
    if ($quarLocId > 0) {
      $pdo->prepare("INSERT INTO inventory_movements
          (item_id, lot_id, movement_type, qty, from_location_id, to_location_id, ref_type, ref_id, moved_by)
          VALUES (?, NULL, 'IN', ?, NULL, ?, 'GRN_REJECT', ?, ?)")
          ->execute([(int)($l['item_id'] ?? 0), $rej, $quarLocId, $grn_id, (int)($_SESSION['user']['id'] ?? 0)]);
    }
  }
}
$this->m->addLine(
          $grn_id,
          (int)($l['po_item_id'] ?? 0),
          (int)($l['item_id'] ?? 0),
          trim((string)($l['lot_no'] ?? '')),
          ($l['expiry_date'] ?? null),
          $qty,
          $location_id
        );
      }

      // Update PO status
      $chk = $pdo->prepare("SELECT SUM(qty_ordered - qty_received) AS rem FROM po_items WHERE po_id = ?");
      $chk->execute([$po_id]);
      $rem = (int)($chk->fetch()['rem'] ?? 0);

      $pdo->prepare("UPDATE purchase_orders SET status = ? WHERE id = ?")
          ->execute([$rem > 0 ? 'PARTIAL_RECEIVED' : 'RECEIVED', $po_id]);

      // Mark related shipment as DELIVERED and log event
try {
  $st = $pdo->prepare("UPDATE shipments SET status='DELIVERED', delivered_at=NOW() WHERE po_id=? AND status <> 'DELIVERED'");
  $st->execute([$po_id]);
  $ev = $pdo->prepare("INSERT INTO shipment_events (shipment_id, status, event_time, remarks)
                       SELECT s.id, 'DELIVERED', NOW(), 'Auto-set on GRN posting' FROM shipments s WHERE s.po_id = ? AND s.status = 'DELIVERED'");
  $ev->execute([$po_id]);
} catch (Throwable $e2) {
  // ignore shipment update failures
}

      $pdo->commit();

      // Attempt to send GRN to billing system (non-blocking).
      try {
        $billingCfg = $this->app->config['billing'] ?? [];
        if (!empty($billingCfg['api_url']) && !empty($billingCfg['api_key'])) {
          $billingSvc = new BillingService($this->app);
          // Fire-and-forget: any exception is caught and logged so it doesn't break the user flow
          $billingSvc->sendGrnToBilling($grn_id);
        }
      } catch (Throwable $be) {
        error_log('BillingService error sending GRN ' . ($grn_id ?? 'unknown') . ': ' . $be->getMessage());
      }

      $this->app->redirect('grn?posted=1');

    } catch (Throwable $e) {
      $pdo->rollBack();
      http_response_code(500);
      echo "Error: " . $e->getMessage();
    }
  }
}