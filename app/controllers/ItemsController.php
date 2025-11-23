<?php
require_once __DIR__ . '/../models/ItemModel.php';

class ItemsController {
  private App $app;
  private ItemModel $m;

  public function __construct(App $app){ $this->app = $app; $this->m = new ItemModel($app->db); }

  public function index(): void {
    $this->app->requireLogin();
    $q = trim($_GET['q'] ?? '');
    $items = $this->m->list($q);
    $this->app->view('items/index', compact('items','q'));
  }

  public function create(): void {
    $this->app->requireLogin();
    $this->app->view('items/create');
  }

  public function store(): void {
    $this->app->requireLogin();
    $this->app->requireCsrf();
    $this->m->create($_POST ?? []);
    $this->app->redirect('items');
  }

  public function edit(): void {
    $this->app->requireLogin();
    $id = (int)($_GET['id'] ?? 0);
    $item = $this->m->find($id);
    if (!$item) { http_response_code(404); exit('Item not found'); }
    $this->app->view('items/edit', compact('item'));
  }

  public function update(): void {
    $this->app->requireLogin();
    $this->app->requireCsrf();
    $id = (int)($_POST['id'] ?? 0);
    $d  = $_POST ?? [];
    foreach (['is_controlled','expiry_required','lot_tracking','active'] as $f) {
      $d[$f] = isset($d[$f]) ? 1 : 0;
    }
    $this->m->update($id, $d);
    $this->app->redirect('items');
  }

  public function importForm(): void {
    $this->app->requireLogin();
    $this->app->view('items/import');
  }

  public function import(): void {
    $this->app->requireLogin();
    $this->app->requireCsrf();
    if (empty($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
      $_SESSION['flash'] = 'Please select a valid CSV file.';
      $this->app->redirect('items');
    }
    $tmp = $_FILES['csv_file']['tmp_name'];
    $fh = fopen($tmp, 'r');
    if (!$fh) { $_SESSION['flash'] = 'Unable to open uploaded file.'; $this->app->redirect('items'); }

    $header = fgetcsv($fh);
    $cols = array_map('trim', $header ?: []);
    $count = 0; $errors = [];
    while (($row = fgetcsv($fh)) !== false) {
      $data = array_combine($cols, $row);
      if (!$data) { continue; }
      // Map CSV fields to item fields (minimal)
      $d = [
        'sku' => $data['sku'] ?? '',
        'name' => $data['name'] ?? '',
        'category' => $data['category'] ?? '',
        'uom' => $data['uom'] ?? 'EA',
        'reorder_point' => (int)($data['reorder_point'] ?? 0),
        'min_stock' => (int)($data['min_stock'] ?? 0),
        'max_stock' => (int)($data['max_stock'] ?? 0),
        'active' => ($data['active'] ?? '1') === '1' ? 1 : 0,
        'is_controlled' => ($data['is_controlled'] ?? '0') === '1' ? 1 : 0,
        'expiry_required' => ($data['expiry_required'] ?? '0') === '1' ? 1 : 0,
        'lot_tracking' => ($data['lot_tracking'] ?? '0') === '1' ? 1 : 0,
      ];
      try {
        $this->m->create($d);
        $count++;
      } catch (Throwable $e) {
        $errors[] = "Row failed: " . ($data['sku'] ?? 'unknown') . " - " . $e->getMessage();
      }
    }
    fclose($fh);
    $_SESSION['flash'] = "Imported: $count items." . (!empty($errors) ? ' Some rows failed.' : '');
    $this->app->redirect('items');
  }
}
