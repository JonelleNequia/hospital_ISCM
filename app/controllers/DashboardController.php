<?php
require_once __DIR__ . '/../models/Dashboard.php';

class DashboardController {
  private $app;
  public function __construct($app){ $this->app=$app; }

  public function index(){
    $this->app->requireLogin();
    $m = new Dashboard($this->app->db);
    $this->app->view('dashboard/index',[
      'kpis' => $m->kpis(),
      'recent_pos' => $m->recentPOs(),
      'latest_shipments' => $m->latestShipments()
    ]);
  }
}
