<?php
require_once __DIR__ . '/../models/IssueModel.php';
require_once __DIR__ . '/../models/RequisitionModel.php';
require_once __DIR__ . '/../helpers/Authz.php';

class IssuesController {
  private $app, $m, $reqM;
  public function __construct($app){
    $this->app=$app;
    $this->m=new IssueModel($app->db);
    $this->reqM=new RequisitionModel($app->db);
  }
  public function index(){
    $this->app->requireLogin();
    requireRole(['Admin','InventoryClerk','Pharmacy']);
    $issues=$this->m->listIssues();
    $this->app->view('issues/index', compact('issues'));
  }
  public function create(){
    $this->app->requireLogin();
    requireRole(['Admin','InventoryClerk','Pharmacy']);
    $req_id=(int)($_GET['requisition_id']??0);
    $requisitions=$this->m->openRequisitions();
    $locations=$this->m->locations();
    $header=$req_id?$this->m->getRequisition($req_id):null;
    $lines=$req_id?$this->m->getRequisitionLines($req_id):[];
    $this->app->view('issues/create', compact('requisitions','locations','header','lines','req_id'));
  }
  public function store(){
    $this->app->requireLogin();
    requireRole(['Admin','InventoryClerk','Pharmacy']);
    $this->app->requireCsrf();
    try{
      $id=$this->m->createIssue($_POST??[], (int)$_SESSION['user']['id']);
      $this->app->redirect('issues');
    }catch(\Throwable $e){
      http_response_code(400);
      echo "Issue failed: ".htmlspecialchars($e->getMessage());
    }
  }
}
