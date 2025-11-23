<?php
require_once __DIR__ . '/../app/core/App.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/ListController.php';

$app = new App();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = rtrim($app->config['app']['base_url'],'/');
if ($base && str_starts_with($path, $base)) {
  $path = substr($path, strlen($base));
}
$path = ltrim($path, '/');

// Simple router using switch on $path
switch (true) {
  case $path === '' || $path === '/':
    (new DashboardController($app))->index();
    break;

  case $path === 'login':
    (new AuthController($app))->login();
    break;
  case $path === 'logout':
    (new AuthController($app))->logout();
    break;

  // Master lists
// Items (CRUD)
  case $path === 'items/create':
    require_once __DIR__ . '/../app/controllers/ItemsController.php';
    (new ItemsController($app))->create();
    break;
  case $path === 'items/import':
    require_once __DIR__ . '/../app/controllers/ItemsController.php';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      (new ItemsController($app))->import();
    } else {
      (new ItemsController($app))->importForm();
    }
    break;
  case $path === 'items/store' && $_SERVER['REQUEST_METHOD']==='POST':
    require_once __DIR__ . '/../app/controllers/ItemsController.php';
    (new ItemsController($app))->store();
    break;
  case $path === 'items/edit':
    require_once __DIR__ . '/../app/controllers/ItemsController.php';
    (new ItemsController($app))->edit();
    break;
  case $path === 'items/update' && $_SERVER['REQUEST_METHOD']==='POST':
    require_once __DIR__ . '/../app/controllers/ItemsController.php';
    (new ItemsController($app))->update();
    break;
  case $path === 'items':
    (new ListController($app))->items();
    break;
  case $path === 'pos':
    (new ListController($app))->pos();
    break;
  
  // Shipments
  case $path === 'shipments/create':
    require_once __DIR__ . '/../app/controllers/ShipmentsController.php';
    (new ShipmentController($app))->create();
    break;
  case $path === 'shipments/store' && $_SERVER['REQUEST_METHOD']==='POST':
    require_once __DIR__ . '/../app/controllers/ShipmentsController.php';
    (new ShipmentController($app))->store();
    break;

  case $path === 'shipments':
    (new ListController($app))->shipments();
    break;
  case $path === 'grn':
    (new ListController($app))->grn();
    break;
  case $path === 'requisitions':
    (new ListController($app))->requisitions();
    break;
  case $path === 'billing':
    require_once __DIR__ . '/../app/controllers/BillingController.php';
    (new BillingController($app))->index();
    break;
  case $path === 'billing/view':
    require_once __DIR__ . '/../app/controllers/BillingController.php';
    (new BillingController($app))->view();
    break;
  case $path === 'billing/retry' && $_SERVER['REQUEST_METHOD'] === 'POST':
    require_once __DIR__ . '/../app/controllers/BillingController.php';
    (new BillingController($app))->retry();
    break;
  case $path === 'inventory':
    (new ListController($app))->inventory();
    break;
  
    // Requisitions
  case $path === 'requisitions/create':
  require_once __DIR__ . '/../app/controllers/RequisitionsController.php';
  (new RequisitionsController($app))->create();
  break;

case $path === 'requisitions/store' && $_SERVER['REQUEST_METHOD'] === 'POST':
  require_once __DIR__ . '/../app/controllers/RequisitionsController.php';
  (new RequisitionsController($app))->store();
  break;

  // Suppliers
  case $path === 'suppliers':
    require_once __DIR__ . '/../app/controllers/SuppliersController.php';
    (new SuppliersController($app))->index();
  break;
  case $path === 'suppliers/create':
    require_once __DIR__ . '/../app/controllers/SuppliersController.php';
    (new SuppliersController($app))->create();
  break;
  case $path === 'suppliers/edit':
    require_once __DIR__ . '/../app/controllers/SuppliersController.php';
    (new SuppliersController($app))->edit();
    break;
  case $path === 'suppliers/update' && $_SERVER['REQUEST_METHOD']==='POST':
    require_once __DIR__ . '/../app/controllers/SuppliersController.php';
    (new SuppliersController($app))->update();
    break;
  case $path === 'suppliers/store' && $_SERVER['REQUEST_METHOD']==='POST':
    require_once __DIR__ . '/../app/controllers/SuppliersController.php';
    (new SuppliersController($app))->store();
  break;

  // Purchase Orders
  case $path === 'pos/create':
    require_once __DIR__ . '/../app/controllers/POController.php';
    (new POController($app))->create();
    break;
  case $path === 'pos/store' && $_SERVER['REQUEST_METHOD'] === 'POST':
    require_once __DIR__ . '/../app/controllers/POController.php';
    (new POController($app))->store();
  break;

  // GRN (Receiving)
  // ...
case $path === 'grn/create':
  require_once __DIR__ . '/../app/controllers/GRNController.php';
  (new GRNController($app))->create();
  break;

case $path === 'grn/store' && $_SERVER['REQUEST_METHOD']==='POST':
  require_once __DIR__ . '/../app/controllers/GRNController.php';
  (new GRNController($app))->store();
  break;
// ...

  // Issues (department issues)
  case $path === 'issues/create':
    require_once __DIR__ . '/../app/controllers/IssuesController.php';
    (new IssuesController($app))->create();
    break;
  case $path === 'issues/store' && $_SERVER['REQUEST_METHOD']==='POST':
    require_once __DIR__ . '/../app/controllers/IssuesController.php';
    (new IssuesController($app))->store();
    break;

  // Dev: send billing report for a GRN (manual testing)
  case $path === 'dev/send_billing_report':
    require_once __DIR__ . '/../app/controllers/ReportsController.php';
    (new ReportsController($app))->send();
    break;

  // Users (admin)
  case $path === 'users':
    require_once __DIR__ . '/../app/controllers/UsersController.php';
    (new UsersController($app))->index();
    break;
  case $path === 'users/create':
    require_once __DIR__ . '/../app/controllers/UsersController.php';
    (new UsersController($app))->create();
    break;

  case $path === 'account/change_password':
    require_once __DIR__ . '/../app/controllers/AccountController.php';
    (new AccountController($app))->changePassword();
    break;

  // Transfers (location to location)
  case $path === 'transfers/create':
    require_once __DIR__ . '/../app/controllers/TransfersController.php';
    (new TransfersController($app))->create();
    break;
  case $path === 'transfers/store' && $_SERVER['REQUEST_METHOD']==='POST':
    require_once __DIR__ . '/../app/controllers/TransfersController.php';
    (new TransfersController($app))->store();
    break;

  // Adjustments
  case $path === 'adjustments/create':
    require_once __DIR__ . '/../app/controllers/AdjustmentsController.php';
    (new AdjustmentsController($app))->create();
    break;
  case $path === 'adjustments/store' && $_SERVER['REQUEST_METHOD']==='POST':
    require_once __DIR__ . '/../app/controllers/AdjustmentsController.php';
    (new AdjustmentsController($app))->store();
    break;

  // Pharmacy system integration: accept stock updates
  case $path === 'api/pharmacy/update_stock' && $_SERVER['REQUEST_METHOD']==='POST':
    require_once __DIR__ . '/../app/controllers/PharmacyController.php';
    (new PharmacyController($app))->updateStock();
    break;

  default:
    http_response_code(404);
    echo "<h1>404</h1>";
}
