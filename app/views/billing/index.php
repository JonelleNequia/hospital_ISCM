<?php /** @var App $app */ $base = $app->baseUrl(); ?>
<h1>Invoice Deliveries</h1>
<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>GRN No</th>
        <th>PO No</th>
        <th>Sent At</th>
        <th>Status</th>
        <th>HTTP</th>
        <th>Attempts</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="8">No deliveries yet.</td></tr>
      <?php else: foreach ($rows as $r): ?>
        <tr>
          <td><?=htmlspecialchars($r['report_id'])?></td>
          <td><?=htmlspecialchars($r['grn_no'] ?? '')?></td>
          <td><?=htmlspecialchars($r['po_no'] ?? '')?></td>
          <td><?=htmlspecialchars($r['sent_at'] ?? '')?></td>
          <td><?=htmlspecialchars($r['status'] ?? '')?></td>
          <td><?=htmlspecialchars($r['http_code'] ?? '')?></td>
          <td><?=htmlspecialchars($r['attempts'] ?? 0)?></td>
          <td>
            <a class="btn" href="<?=$base?>/billing/view?id=<?=urlencode($r['report_id'])?>">View</a>
            <form style="display:inline" method="post" action="<?=$base?>/billing/retry">
              <input type="hidden" name="id" value="<?=htmlspecialchars($r['report_id'])?>">
              <button class="btn">Retry</button>
            </form>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
