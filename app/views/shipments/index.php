<?php /** @var App $app */ $base = $app->baseUrl(); ?>
<h1>Shipments</h1>
<div class="card">
  <div class="searchbar">
    <div style="margin-left:auto">
      <a class="btn" href="<?=$base?>/shipments/create">New Shipment</a>
    </div>
  </div>
  <table class="table">
    <thead><tr><th>ID</th><th>PO</th><th>Carrier</th><th>Tracking No.</th><th>Status</th><th>ETA</th></tr></thead>
    <tbody>
    <?php foreach ((($shipments ?? null) ?: ($rows ?? [])) as $s): ?>
      <tr>
        <td><?=htmlspecialchars($s['id'] ?? '')?></td>
        <td><?=htmlspecialchars($s['po_no'] ?? '')?></td>
        <td><?=htmlspecialchars($s['carrier'] ?? '')?></td>
        <td><?=htmlspecialchars($s['tracking_no'] ?? '')?></td>
        <td><?=htmlspecialchars($s['status'] ?? '')?></td>
        <td><?=htmlspecialchars($s['eta'] ?? '')?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
