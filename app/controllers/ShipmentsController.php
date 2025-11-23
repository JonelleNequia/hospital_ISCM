<?php
require_once __DIR__ . '/../models/ShipmentModel.php';
require_once __DIR__ . '/../helpers/Authz.php';

class ShipmentController {
  private $app, $m;
  public function __construct($app){ $this->app=$app; $this->m=new ShipmentModel($app->db); }

  public function index(){
    $this->app->requireLogin();
    requireRole(['Admin','Procurement','Receiver']);
    $shipments=$this->m->listShipments();
    $this->app->view('shipments/index', compact('shipments'));
  }
  public function create(){
    $this->app->requireLogin();
    requireRole(['Admin','Procurement']);
    $pos=$this->m->listPOs();
    $this->app->view('shipments/create', compact('pos'));
  }
  public function store(){
    $this->app->requireLogin();
    requireRole(['Admin','Procurement']);
    $this->app->requireCsrf();
    $this->m->createShipment($_POST??[], (int)$_SESSION['user']['id']);
    $this->app->redirect('shipments');
  }
  public function events(){
    $this->app->requireLogin();
    requireRole(['Admin','Procurement','Receiver']);
    $shipment_id=(int)($_GET['id']??0);
    $header=$this->m->getShipment($shipment_id);
    $events=$this->m->events($shipment_id);
    $this->app->view('shipments/events', compact('header','events'));
  }
  public function eventStore(){
    $this->app->requireLogin();
    requireRole(['Admin','Procurement','Receiver']);
    $this->app->requireCsrf();
    $this->m->addEvent($_POST??[]);
    $this->app->redirect('shipments');
  }
}
