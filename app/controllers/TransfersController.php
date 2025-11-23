<?php
require_once __DIR__ . '/../models/TransferModel.php';
require_once __DIR__ . '/../helpers/Authz.php';

class TransfersController {
  private $app, $m;
  public function __construct($app){ $this->app=$app; $this->m=new TransferModel($app->db); }

  public function create(){
    $this->app->requireLogin();
    requireRole(['Admin','InventoryClerk']);
    $locations=$this->m->locations();
    $this->app->view('transfers/create', compact('locations'));
  }
  public function store(){
    $this->app->requireLogin();
    requireRole(['Admin','InventoryClerk']);
    $this->app->requireCsrf();
    try{
      $this->m->transfer($_POST??[], (int)$_SESSION['user']['id']);
      $this->app->redirect('dashboard');
    }catch(\Throwable $e){
      http_response_code(400); echo "Transfer failed: ".htmlspecialchars($e->getMessage());
    }
  }
}
