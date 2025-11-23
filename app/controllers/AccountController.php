<?php
class AccountController {
  private $app;
  private $pdo;
  public function __construct($app){ $this->app = $app; $this->pdo = $app->db; }

  // Change password (GET shows form, POST processes)
  public function changePassword(){
    $this->app->requireLogin();
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
      $this->app->requireCsrf();
      $current = $_POST['current_password'] ?? '';
      $new = $_POST['new_password'] ?? '';
      $confirm = $_POST['confirm_password'] ?? '';

      if ($new !== $confirm){ $_SESSION['flash_error'] = 'New password and confirmation do not match'; $this->app->redirect('account/change_password'); }
      if (strlen($new) < 8){ $_SESSION['flash_error'] = 'Password must be at least 8 characters'; $this->app->redirect('account/change_password'); }

      $userId = (int)($_SESSION['user']['id'] ?? 0);
      if ($userId <= 0){ $_SESSION['flash_error'] = 'Not logged in'; $this->app->redirect('login'); }

      $st = $this->pdo->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
      $st->execute([$userId]);
      $hash = $st->fetchColumn();
      if (!$hash || !password_verify($current, $hash)){
        $_SESSION['flash_error'] = 'Current password is incorrect';
        $this->app->redirect('account/change_password');
      }

      $newHash = password_hash($new, PASSWORD_BCRYPT);
      $up = $this->pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
      $up->execute([$newHash, $userId]);

      $_SESSION['flash_ok'] = 'Password changed successfully';
      $this->app->redirect('');
    }

    $this->app->view('account/change_password');
  }
}
