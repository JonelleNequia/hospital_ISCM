<?php
require_once __DIR__ . '/../services/BillingService.php';

class BillingController {
  private $app;
  public function __construct($app){ $this->app = $app; }

  // Invoice dashboard: list billing_reports with related GRN/PO info
  public function index(){
    $this->app->requireLogin();
    $pdo = $this->app->db;
    $sql = "SELECT br.id AS report_id, br.grn_id, br.sent_at, br.status, br.http_code, br.attempts,
                   g.grn_no, g.received_at, p.po_no
            FROM billing_reports br
            LEFT JOIN goods_receipts g ON g.id = br.grn_id
            LEFT JOIN purchase_orders p ON p.id = g.po_id
            ORDER BY br.id DESC LIMIT 200";
    $rows = $pdo->query($sql)->fetchAll();
    $this->app->view('billing/index', compact('rows'));
  }

  // View a single report
  public function view(){
    $this->app->requireLogin();
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) { http_response_code(400); echo 'Invalid id'; return; }
    $pdo = $this->app->db;
    $st = $pdo->prepare("SELECT br.*, g.grn_no, p.po_no FROM billing_reports br LEFT JOIN goods_receipts g ON g.id=br.grn_id LEFT JOIN purchase_orders p ON p.id=g.po_id WHERE br.id = ? LIMIT 1");
    $st->execute([$id]);
    $row = $st->fetch();
    if (!$row) { http_response_code(404); echo 'Not found'; return; }
    $this->app->view('billing/view', compact('row'));
  }

  // Retry sending a report (POST)
  public function retry(){
    $this->app->requireLogin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Method not allowed'; return; }
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) { http_response_code(400); echo 'Invalid id'; return; }
    $pdo = $this->app->db;
    $st = $pdo->prepare("SELECT * FROM billing_reports WHERE id = ? LIMIT 1");
    $st->execute([$id]);
    $r = $st->fetch();
    if (!$r) { http_response_code(404); echo 'Not found'; return; }

    // call BillingService to resend based on grn_id
    try {
      $svc = new BillingService($this->app);
      $result = $svc->sendGrnToBilling((int)$r['grn_id']);

      $status = ($result['http_code'] === 200) ? 'SENT' : 'FAILED';
      $resp = substr((string)($result['response'] ?? ''), 0, 65535);
      $attempts = ((int)$r['attempts']) + 1;
      $upd = $pdo->prepare("UPDATE billing_reports SET status=?, http_code=?, response=?, attempts=?, sent_at=NOW() WHERE id = ?");
      $upd->execute([$status, $result['http_code'], $resp, $attempts, $id]);

      $this->app->redirect('billing');
    } catch (Throwable $e) {
      error_log('BillingController retry error: ' . $e->getMessage());
      http_response_code(500);
      echo 'Error: ' . $e->getMessage();
    }
  }
}
