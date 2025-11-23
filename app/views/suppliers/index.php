<?php /** @var App $app */ $base = $app->baseUrl(); ?>
<h1>Suppliers</h1>
<div style="margin-bottom:1rem">
  <a class="btn" href="<?=$base?>/suppliers/create">New Supplier</a>
</div>
<table class="table">
  <thead>
    <tr>
      <th>Name</th>
      <th>Contact</th>
      <th>Phone</th>
      <th>Email</th>
      <th>Address</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach (($suppliers ?? []) as $s): ?>
      <tr>
        <td><?=htmlspecialchars($s['name'] ?? '')?></td>
        <td><?=htmlspecialchars($s['contact'] ?? '')?></td>
        <td><?=htmlspecialchars($s['phone'] ?? '')?></td>
        <td><?=htmlspecialchars($s['email'] ?? '')?></td>
        <td><?=htmlspecialchars($s['address'] ?? '')?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>