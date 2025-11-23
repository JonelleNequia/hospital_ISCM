<?php?>
<h1>Dashboard</h1>
<div class="grid cols-3">
  <div class="card kpi"><div class="hint">Low Stock</div><div class="value"><?=$kpis['low_stock']??0?></div></div>
  <div class="card kpi"><div class="hint">In-Transit Shipments</div><div class="value"><?=$kpis['in_transit']??0?></div></div>
  <div class="card kpi"><div class="hint">Near Expiry Lots (≤ 60d)</div><div class="value"><?=$kpis['near_expiry']??0?></div></div>
</div>

<div class="grid cols-2" style="margin-top:16px">
  <div class="card">
    <h2>Recent POs</h2>
    <table class="table">
      <thead><tr><th>PO #</th><th>Supplier</th><th>Status</th><th>Expected</th></tr></thead>
      <tbody>
      <?php foreach($recent_pos as $po): ?>
        <tr>
          <td><a href="pos?view=<?=$po['id']?>"><?=htmlspecialchars($po['po_no'])?></a></td>
          <td><?=htmlspecialchars($po['supplier'] ?? '')?></td>
          <td><span class="badge"><?=$po['status']?></span></td>
          <td><?=htmlspecialchars($po['expected_date'] ?? '')?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card">
    <h2>Latest Shipments</h2>
    <table class="table">
      <thead><tr><th>PO #</th><th>Carrier</th><th>Status</th><th>ETA</th></tr></thead>
      <tbody>
      <?php foreach($latest_shipments as $s): ?>
        <tr>
          <td><?=htmlspecialchars($s['po_no'])?></td>
          <td><?=htmlspecialchars($s['carrier'])?></td>
          <td><span class="badge"><?=$s['status']?></span></td>
          <td><?=htmlspecialchars($s['eta'])?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
