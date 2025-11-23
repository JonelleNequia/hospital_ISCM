  <?php
  require_once __DIR__ . '/../models/User.php';

  class AuthController {
    private $app;
    public function __construct($app){ $this->app=$app; }

    private function ensureAdminSeed(){
      // Seed default Admin if users table is empty
      $pdo = $this->app->db;
      $count = (int)$pdo->query("SELECT COUNT(*) c FROM users")->fetch()['c'];
      if ($count === 0) {
        // Ensure Admin role exists
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE name='Admin'");
        $stmt->execute();
        $role = $stmt->fetch();
        if (!$role) {
          $pdo->exec("INSERT INTO roles (name) VALUES ('Admin')");
          $roleId = (int)$pdo->lastInsertId();
        } else {
          $roleId = (int)$role['id'];
        }
        // Create default admin (email/pass below)
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $ins = $pdo->prepare("INSERT INTO users (name,email,password_hash,role_id,status) VALUES (?,?,?,?, 'ACTIVE')");
        $ins->execute(['System Admin','admin@hospital.local',$hash,$roleId]);
      }
    }

    public function login(){
      $this->ensureAdminSeed();

      if ($_SERVER['REQUEST_METHOD']==='POST'){
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $user = (new User($this->app->db))->findByEmail($email);
          if ($user && password_verify($password, $user['password_hash'])) {
          $_SESSION['user'] = [
            'id'        => $user['id'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'role_id'   => $user['role_id'] ?? null,
            'role'      => $user['role'] ?? null,
            'role_name' => $user['role'] ?? null,
          ];
          $this->app->redirect('');
          } else {
            // Log helpful debug info to server error log (does not expose to users)
            if ($user) {
              error_log('AuthController: failed login for user id=' . ($user['id'] ?? 'unknown') . ' email=' . ($email ?? '') . ' - bad password');
            } else {
              error_log('AuthController: failed login, user not found for email=' . ($email ?? ''));
            }
            $this->app->view('auth/login',['error'=>'Invalid credentials.']);
          }
      } else {
        $this->app->view('auth/login');
      }
    }

    public function logout(){
      session_destroy();
      $this->app->redirect('login');
    }
  }
