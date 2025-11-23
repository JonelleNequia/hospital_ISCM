<?php
require_once __DIR__ . '/../models/ListModel.php';

class ListController {
  private $app;
  private $m;
  public function __construct($app){ $this->app=$app; $this->m = new ListModel($app->db); }

  public function items(){
    $this->app->requireLogin();
    $q = $_GET['q'] ?? '';
    $rows = $this->m->items($q);
    $this->app->view('items/index', compact('rows','q'));
  }

  public function pos(){
    $this->app->requireLogin();
    $q = $_GET['q'] ?? '';
    $rows = $this->m->pos($q);
    $this->app->view('pos/index', compact('rows','q'));
  }
  public function shipments(){ $this->app->requireLogin(); $rows = $this->m->shipments(); $this->app->view('shipments/index', compact('rows')); }
  public function grn(){ $this->app->requireLogin(); $rows = $this->m->grn(); $this->app->view('grn/index', compact('rows')); }
  public function inventory(){ $this->app->requireLogin(); $rows = $this->m->inventory(); $this->app->view('inventory/index', compact('rows')); }
  public function requisitions(){
    $this->app->requireLogin();
    $q = $_GET['q'] ?? '';
    $rows = $this->m->requisitions($q);
    $this->app->view('requisitions/index', compact('rows','q'));
  }
}
