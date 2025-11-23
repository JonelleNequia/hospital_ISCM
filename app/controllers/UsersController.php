<?php
require_once __DIR__ . '/../models/User.php';

class UsersController {
  private $app;
  private $pdo;
  public function __construct($app){ $this->app=$app; $this->pdo=$app->db; }

  public function index(){
    $this->app->requireLogin();
    $this->app->requireRole(['Admin']);
    $st = $this->pdo->query('SELECT u.id,u.name,u.email,u.status,r.name as role FROM users u JOIN roles r ON r.id=u.role_id ORDER BY u.id DESC');
    $rows = $st->fetchAll();
    $this->app->view('users/index', ['rows'=>$rows]);
  }

  public function create(){
    $this->app->requireLogin();
    $this->app->requireRole(['Admin']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
      $name = trim($_POST['name'] ?? '');
      $email = strtolower(trim($_POST['email'] ?? ''));
      $password = $_POST['password'] ?? '';
      $role = $_POST['role'] ?? '';
      if ($name === '' || $email === '' || $password === '' || $role === ''){
        $this->app->view('users/create', ['error'=>'Please fill all fields.','roles'=>$this->getRoles()]);
        return;
      }
      // find role id
      $st = $this->pdo->prepare('SELECT id FROM roles WHERE name = ? LIMIT 1');
      $st->execute([$role]);
      $rid = $st->fetchColumn();
      if (!$rid) {
        $this->app->view('users/create', ['error'=>'Invalid role.','roles'=>$this->getRoles()]);
        return;
      }
      $hash = password_hash($password, PASSWORD_BCRYPT);
      $ins = $this->pdo->prepare('INSERT INTO users (role_id,name,email,password_hash,status) VALUES (?,?,?,?,"ACTIVE")');
      $ins->execute([$rid,$name,$email,$hash]);
      $this->app->redirect('users');
    } else {
      $this->app->view('users/create', ['roles'=>$this->getRoles()]);
    }
  }

  private function getRoles(){
    $st = $this->pdo->query('SELECT name FROM roles ORDER BY name');
    return array_map(function($r){ return $r['name']; }, $st->fetchAll());
  }
}
