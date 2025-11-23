<?php /** @var App $app */ $base = $app->baseUrl(); ?>
<h1>Create User</h1>
<?php if(!empty($error)): ?>
  <div class="flash" style="background:#ffecec;border:1px solid #fadada;color:#c91010;padding:10px;border-radius:6px;margin-bottom:12px"><?=htmlspecialchars($error)?></div>
<?php endif; ?>
<div class="card">
  <form method="post" action="<?=$base?>/users/create">
    <label>Name<br><input type="text" name="name" required></label><br>
    <label>Email<br><input type="email" name="email" required></label><br>
    <label>Password<br><input type="password" name="password" required></label><br>
    <label>Role<br>
      <select name="role" required>
        <?php foreach(($roles ?? []) as $r): ?>
          <option value="<?=htmlspecialchars($r)?>"><?=htmlspecialchars($r)?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <p><button class="btn" type="submit">Create</button></p>
  </form>
</div>
