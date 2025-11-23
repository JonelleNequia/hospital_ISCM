<?php /** @var App $app */ $base = $app->baseUrl(); ?>
<h1>Change Password</h1>
<div class="card">
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="flash" style="background:#ffecec;border:1px solid #fadada;color:#c91010;padding:10px;border-radius:6px;margin-bottom:12px"><?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_ok'])): ?>
    <div class="flash" style="background:#e9f7ef;border:1px solid #cfead8;color:#1b6b3a;padding:10px;border-radius:6px;margin-bottom:12px"><?php echo htmlspecialchars($_SESSION['flash_ok']); unset($_SESSION['flash_ok']); ?></div>
  <?php endif; ?>

  <form method="post" action="<?=$base?>/account/change_password" class="form">
    <input type="hidden" name="csrf" value="<?=$app->csrfToken()?>">
    <label class="label">Current Password<br><input class="input" type="password" name="current_password" required></label>
    <label class="label">New Password<br><input class="input" type="password" name="new_password" required></label>
    <label class="label">Confirm New Password<br><input class="input" type="password" name="confirm_password" required></label>
    <p><button class="btn" type="submit">Change Password</button> <a class="btn" href="<?=$base?>/" style="background:#777">Cancel</a></p>
  </form>
</div>
