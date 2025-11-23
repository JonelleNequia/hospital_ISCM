<?php
require_once __DIR__ . '/../models/POModel.php';
require_once __DIR__ . '/../helpers/Authz.php';

class POController {
  private $app, $m;
  public function __construct($app){ $this->app = $app; $this->m = new POModel($app->db); }

  public function index(){
    $this->app->requireLogin();
    requireRole(['Admin','Procurement']);
    $pos = $this->m->listPOs();
    $this->app->view('pos/index', compact('pos'));
  }

  public function create(){
  $this->app->requireLogin();
  requireRole(['Admin','Procurement']);

  $suppliers = $this->m->supplierOptions();
  $items     = $this->m->itemOptions();

  // Generate one-time nonce for idempotent submit
  if (!isset($_SESSION['form_nonces'])) $_SESSION['form_nonces'] = [];
  $nonce = bin2hex(random_bytes(16));
  $_SESSION['form_nonces']['po_store'][$nonce] = time();

  $this->app->view('pos/create', compact('suppliers','items','nonce'));
}

  public function store(){
  $this->app->requireLogin();
  requireRole(['Admin','Procurement']);
  $this->app->requireCsrf();

  // Idempotency: accept each form submit only once
  $nonce = $_POST['nonce'] ?? '';
  if (empty($nonce) || empty($_SESSION['form_nonces']['po_store'][$nonce])) {

    $_SESSION['flash_error'] = 'Duplicate or invalid submission.';
    $this->app->redirect('pos');
  }
  unset($_SESSION['form_nonces']['po_store'][$nonce]);

  $po_id = $this->m->createPO($_POST ?? [], (int)($_SESSION['user']['id'] ?? 0));

  $lines = $_POST['lines'] ?? [];
  foreach ($lines as $ln) {
    $item_id = (int)($ln['item_id'] ?? 0);
    $qty     = (int)($ln['qty'] ?? 0);
    $price   = (float)($ln['price'] ?? 0.0);
    if ($item_id > 0 && $qty > 0) {
      $this->m->addLine($po_id, $item_id, $qty, $price);
    }
  }

  $this->app->redirect('pos');
}
}