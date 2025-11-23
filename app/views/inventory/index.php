<?php?>
<?php $base = rtrim($app->config['app']['base_url'],'/'); ?>
<h1>Inventory (On-hand by Lot)</h1>
<div class="card">
  <table class="table">
    <thead><tr><th>sku</th><th>name</th><th>lot_no</th><th>expiry_date</th><th>location</th><th>qty_on_hand</th></tr></thead>
    <tbody>
    <?php foreach($rows as $row): ?><tr>
      <td><?=htmlspecialchars($row['sku'] ?? '')?></td>
      <td><?=htmlspecialchars($row['name'] ?? '')?></td>
      <td><?=htmlspecialchars($row['lot_no'] ?? '')?></td>
      <td><?=htmlspecialchars($row['expiry_date'] ?? '')?></td>
      <td><?=htmlspecialchars($row['location'] ?? '')?></td>
      <td><?=htmlspecialchars($row['qty_on_hand'] ?? '')?></td>
    </tr><?php endforeach; ?>
    </tbody>
  </table>
</div>
