<?php
require_once __DIR__ . '/../models/DepartmentModel.php';
require_once __DIR__ . '/../models/RequisitionModel.php'; // make sure the class name inside is RequisitionModel

class RequisitionsController {
  private $app;
  private $m;     // RequisitionModel
  private $dept;  // DepartmentModel

  public function __construct($app){
    $this->app  = $app;
    $this->m    = new RequisitionModel($app->db);  // <-- use the actual class name in RequisitionModel.php
    $this->dept = new DepartmentModel($app->db);
  }

  public function create(){
  $this->app->requireLogin();
  $this->app->requireRole(['Admin','DeptReq','Pharmacy','InventoryClerk']); // ✅ fixed

  $departments = $this->dept->listAll();
  $items = $this->app->db
    ->query("SELECT id, sku, name FROM items WHERE active=1 ORDER BY name")
    ->fetchAll(PDO::FETCH_ASSOC);

  $this->app->view('requisitions/create', compact('departments','items'));
}

  public function store(){
    $this->app->requireLogin();
    $this->app->requireRole(['Admin','DeptReq','Pharmacy','InventoryClerk']);
    $this->app->requireCsrf();

    $pdo = $this->app->db;

    $dept_id = (int)($_POST['department_id'] ?? 0);
    if ($dept_id <= 0 || !$this->dept->exists($dept_id)) {
      $_SESSION['flash_error'] = 'Valid department is required';
      $this->app->redirect('requisitions/create');
    }

    $priority = in_array(($_POST['priority'] ?? 'NORMAL'), ['LOW','NORMAL','HIGH'], true) ? $_POST['priority'] : 'NORMAL';
    $requested_by = (int)($_SESSION['user']['id'] ?? 0);
    if ($requested_by <= 0) { $_SESSION['flash_error'] = 'Not logged in'; $this->app->redirect('login'); }

    $lines = $_POST['lines'] ?? [];

    // Require at least one valid line
    $hasQty = false;
    foreach (($lines ?: []) as $ln) {
      if ((int)($ln['qty_requested'] ?? 0) > 0 && (int)($ln['item_id'] ?? 0) > 0) { $hasQty = true; break; }
    }
    if (!$hasQty) {
      $_SESSION['flash_error'] = 'Add at least one item with quantity';
      $this->app->redirect('requisitions/create');
    }

    $pdo->beginTransaction();
    try {
      // Create header
      $st = $pdo->prepare("
        INSERT INTO requisitions (req_no, department_id, requested_by, status, priority, created_at)
        VALUES (?, ?, ?, 'SUBMITTED', ?, NOW())
      ");
      // simple req no generator (replace with your own if you have one)
      $reqNo = 'REQ-' . str_pad((string)(time() % 1000000), 6, '0', STR_PAD_LEFT);
      $st->execute([$reqNo, $dept_id, $requested_by, $priority]);
      $req_id = (int)$pdo->lastInsertId();

      // Lines
      $ins = $pdo->prepare("INSERT INTO requisition_items (requisition_id, item_id, qty_requested) VALUES (?, ?, ?)");
      foreach ($lines as $ln) {
        $item = (int)($ln['item_id'] ?? 0);
        $qty  = (int)($ln['qty_requested'] ?? 0);
        if ($item > 0 && $qty > 0) {
          $ins->execute([$req_id, $item, $qty]);
        }
      }

      $pdo->commit();
      $_SESSION['flash_ok'] = 'Requisition created';
      $this->app->redirect('requisitions'); // or show page
    } catch (Throwable $e) {
      $pdo->rollBack();
      $_SESSION['flash_error'] = 'Failed to create requisition: '.$e->getMessage();
      $this->app->redirect('requisitions/create');
    }
  }
}