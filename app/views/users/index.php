<?php /** @var App $app */ $base = $app->baseUrl(); ?>
<h1>Users</h1>
<?php if(empty($rows)): ?>
  <div class="card">No users found.</div>
<?php else: ?>
  <div class="card">
    <p><a class="btn" href="<?=$base?>/users/create">Create User</a></p>
    <table class="table">
      <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?=htmlspecialchars($r['id'])?></td>
          <td><?=htmlspecialchars($r['name'])?></td>
          <td><?=htmlspecialchars($r['email'])?></td>
          <td><?=htmlspecialchars($r['role'])?></td>
          <td><?=htmlspecialchars($r['status'])?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
