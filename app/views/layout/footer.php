<?php
  $pathNow = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
  $isLogin = str_contains($pathNow, '/login');
?>
<?php if(!$isLogin): ?>
<?php require __DIR__ . '/../partials/ui_foot.php'; ?>
<?php endif; ?>
</body>
</html>
