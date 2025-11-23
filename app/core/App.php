<?php
class App {
  public array $config;
  public PDO $db;

  public function __construct() {
    $this->config = require __DIR__ . '/../config/config.php';
    session_name($this->config['app']['session_name'] ?? 'HOSPITAL_SCMS');
if (PHP_SESSION_ACTIVE !== session_status()) {
  // Secure session cookie flags
  $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
  session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => $secure
  ]);
  session_start();
}
    $this->db = $this->makePDO();
  
    // Dev error reporting
    ini_set('display_errors','1');
    error_reporting(E_ALL);
    set_error_handler(function($severity,$message,$file,$line){ throw new ErrorException($message, 0, $severity, $file, $line); });
    set_exception_handler(function($e){ http_response_code(500); echo '<pre style=\"white-space:pre-wrap\">'.htmlspecialchars((string)$e).'</pre>'; });
}

  private function makePDO(): PDO {
    $c = $this->config['db'];
    $__pdo_tmp = new PDO(

      $c['dsn'], $c['user'], $c['pass'],
      $c['options'] ?? [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC ]
      );
  // Enforce session-level settings for consistency with schema
  $__pdo_tmp->exec("SET SESSION time_zone = '+00:00'");
  $__pdo_tmp->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
  return $__pdo_tmp;
}

public function view(string $tpl, array $vars = []): void {
    $app = $this;
    if (!empty($vars)) {
        extract($vars, EXTR_OVERWRITE);
    }
    require __DIR__ . '/../views/layout/header.php';  // load CSS, nav
    require __DIR__ . '/../views/' . $tpl . '.php';   // actual content
    require __DIR__ . '/../views/layout/footer.php';  // close HTML
  }

  public function baseUrl(): string {
    return rtrim($this->config['app']['base_url'] ?? '/hospital_ISCM/public', '/');
  }

  public function redirect(string $to): void {
    header('Location: ' . $this->baseUrl() . '/' . ltrim($to,'/'));
    exit;
  }

  // Simple CSRF (already used by some forms)
  public function csrfToken(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
  }
  public function requireCsrf(): void {
    if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? null)) {
      http_response_code(419); exit('CSRF token mismatch');
    }
  }

  public function requireLogin(): void {
    if (empty($_SESSION['user'])) $this->redirect('login');
  }

  public function requireRole(array $allowed): void {
    if (empty($_SESSION['user'])) {
      $_SESSION['flash_error'] = 'Please login';
      $this->redirect('login');
    }

    $user = $_SESSION['user'];
    $roleName = $user['role_name'] ?? ($user['role'] ?? null);

    if (!$roleName) {
      $roleId = (int)($user['role_id'] ?? 0);
      if ($roleId > 0) {
        $st = $this->db->prepare("SELECT name FROM roles WHERE id=? LIMIT 1");
        $st->execute([$roleId]);
        $resolved = (string)($st->fetchColumn() ?: '');
        if ($resolved !== '') {
          $roleName = $resolved;
          $_SESSION['user']['role_name'] = $resolved;
        }
      }
    }

    if (!$roleName || !in_array($roleName, $allowed, true)) {
      $_SESSION['flash_error'] = 'Access denied';
      $this->redirect('');
    }
  }
}
