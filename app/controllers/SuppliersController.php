<?php
require_once __DIR__ . '/../models/SupplierModel.php';
require_once __DIR__ . '/../helpers/Authz.php';

class SuppliersController {
  private $app;
  private $m;

  public function __construct($app){
    $this->app = $app;
    $this->m   = new SupplierModel($app->db);
  }

  // GET /suppliers
  public function index(){
    $this->app->requireLogin();
    requireRole(['Admin','Procurement']);
    $suppliers = $this->m->listAll();
    $this->app->view('suppliers/index', compact('suppliers'));
  }

  // GET /suppliers/create
  public function create(){
    $this->app->requireLogin();
    requireRole(['Admin','Procurement']);
    $this->app->view('suppliers/create');
  }

  // GET /suppliers/edit?id=123
  public function edit(){
    $this->app->requireLogin();
    requireRole(['Admin','Procurement']);
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) { $_SESSION['flash_error'] = 'Invalid supplier id'; $this->app->redirect('suppliers'); }
    $supplier = $this->m->find($id);
    if (!$supplier) { $_SESSION['flash_error'] = 'Supplier not found'; $this->app->redirect('suppliers'); }
    $this->app->view('suppliers/edit', compact('supplier'));
  }

  // POST /suppliers/update
  public function update(){
    $this->app->requireLogin();
    requireRole(['Admin','Procurement']);
    $this->app->requireCsrf();
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) { $_SESSION['flash_error'] = 'Invalid supplier id'; $this->app->redirect('suppliers'); }
    $this->m->update($id, $_POST);
    $_SESSION['flash_ok'] = 'Supplier updated';
    $this->app->redirect('suppliers');
  }

  // POST /suppliers/store
  public function store(){
    $this->app->requireLogin();
    requireRole(['Admin','Procurement']);
    $this->app->requireCsrf();

    $name = trim($_POST['name'] ?? '');
    if ($name === ''){
      $_SESSION['flash_error'] = 'Supplier name is required';
      $this->app->redirect('suppliers/create');
    }

    $this->m->create([
      'name'    => $name,
      'contact' => trim($_POST['contact'] ?? ''),
      'phone'   => trim($_POST['phone'] ?? ''),
      'email'   => trim($_POST['email'] ?? ''),
      'address' => trim($_POST['address'] ?? ''),
    ]);

    $_SESSION['flash_ok'] = 'Supplier created';
    $this->app->redirect('suppliers');
  }
}