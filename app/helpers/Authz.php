<?php
function requireRole(array $allowed): void {
  if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo 'Unauthorized. Please login.';
    exit;
  }

  $roleName = $_SESSION['user']['role_name'] ?? ($_SESSION['user']['role'] ?? null);

  if ($roleName === 'Admin') return;

  if (!$roleName || !in_array($roleName, $allowed, true)) {
    http_response_code(403);
    echo 'Forbidden: insufficient permissions.';
    exit;
  }
}

