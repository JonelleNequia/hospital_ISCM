<?php $app = $app ?? null; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>EVERGREEN MEDICAL HOSPITAL</title>
  <base href="<?= rtrim($app->config['app']['base_url'], '/') . '/' ?>">
  <link rel="stylesheet" href="assets/css/style.css"/>
  <script src="https://unpkg.com/lottie-web/build/player/lottie.min.js"></script>
  <?php $base = $app->baseUrl(); ?>
  <script defer src="<?=$base?>/assets/js/app.js"></script>
  <script>
    // Quick runtime check to help diagnose missing functions
    (function(){
      console.info('Header: scripts referenced via base <?=$base?>');
      window._app_header_checked = true;
    })();
  </script>
</head>
<body>

<?php require __DIR__ . '/../partials/ui_head.php'; ?>
<?php
  $pathNow = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
  $isLogin = str_contains($pathNow, '/login');
?>


