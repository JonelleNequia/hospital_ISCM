<?php
require_once __DIR__ . '/../models/GRNModel.php';
require_once __DIR__ . '/../services/BillingService.php';

class ReportsController {
  private $app;
  private $grnModel;

  public function __construct($app) {
    $this->app = $app;
    $this->grnModel = new GRNModel($app->db);
  }

  // Dev/manual endpoint: send GRN report to billing and log the result
  public function send(){
    $this->app->requireLogin();

    $grn_id = (int)($_GET['grn_id'] ?? 0);
    if ($grn_id <= 0) {
      http_response_code(400);
      echo "Invalid grn_id";
      return;
    }

    // Ensure billing configured
    $billingCfg = $this->app->config['billing'] ?? [];
    if (empty($billingCfg['api_url']) || empty($billingCfg['api_key'])) {
      http_response_code(500);
      echo "Billing configuration not set";
      return;
    }

    try {
      $svc = new BillingService($this->app);
      $result = $svc->sendGrnToBilling($grn_id);

      // Persist a record in billing_reports for auditing
      $pdo = $this->app->db;
      $st = $pdo->prepare("INSERT INTO billing_reports (grn_id, sent_at, status, http_code, response, attempts) VALUES (?, NOW(), ?, ?, ?, ?)");
      $status = ($result['http_code'] === 200) ? 'SENT' : 'FAILED';
      $resp = substr((string)($result['response'] ?? ''), 0, 65535);
      $st->execute([$grn_id, $status, $result['http_code'], $resp, 1]);

      header('Content-Type: application/json');
      echo json_encode(['ok' => true, 'result' => $result]);
    } catch (Throwable $e) {
      error_log('ReportsController send error: ' . $e->getMessage());
      http_response_code(500);
      echo "Error: " . $e->getMessage();
    }
  }
}
