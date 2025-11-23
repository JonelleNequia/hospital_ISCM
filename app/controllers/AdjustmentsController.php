<?php
require_once __DIR__ . '/../models/AdjustmentModel.php';
require_once __DIR__ . '/../helpers/Authz.php';

class AdjustmentsController {
  private $app, $m;
  public function __construct($app){ $this->app=$app; $this->m=new AdjustmentModel($app->db); }

  public function create(){
    $this->app->requireLogin();
    requireRole(['Admin','InventoryClerk','Auditor']);
    $this->app->view('adjustments/create');
  }
  public function store(){
    $this->app->requireLogin();
    requireRole(['Admin','InventoryClerk','Auditor']);
    $this->app->requireCsrf();
    try{
      $this->m->adjust($_POST??[], (int)$_SESSION['user']['id']);
      $this->app->redirect('dashboard');
    }catch(\Throwable $e){
      http_response_code(400); echo "Adjustment failed: ".htmlspecialchars($e->getMessage());
    }
  }
}
