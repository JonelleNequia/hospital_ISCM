<?php
/** @var App $app */
$base = rtrim($app->config['app']['base_url'],'/');
$qVal = isset($q) ? $q : ($_GET['q'] ?? '');
?>
<h1>Purchase Orders</h1>
<div class="card">
  <div class="searchbar">
    <form method="get" action="<?=$base?>/pos">
      <input class="input" type="text" name="q" placeholder="Search..." value="<?=htmlspecialchars($qVal)?>">
      <button class="btn" type="submit">Search</button>
    </form>
    <div style="margin-left:auto">
      <a class="btn" href="<?=$base?>/pos/create">New Purchase Order</a>
    </div>
  </div>
  <table class="table">
    <thead>
      <tr><th>PO No</th><th>Supplier</th><th>Status</th><th>Expected Date</th></tr>
    </thead>
    <tbody>
    <?php foreach ((($pos ?? null) ?: ($rows ?? [])) as $row): ?>
      <tr>
        <td><?=htmlspecialchars($row['po_no'] ?? '')?></td>
        <td><?=htmlspecialchars($row['supplier'] ?? '')?></td>
        <td><?=htmlspecialchars($row['status'] ?? '')?></td>
        <td><?=htmlspecialchars($row['expected_date'] ?? '')?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
