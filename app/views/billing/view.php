<?php /** @var App $app */ $base = $app->baseUrl(); ?>
<h1>Delivery Detail</h1>
<div class="card">
  <table class="table">
    <tr><th>ID</th><td><?=htmlspecialchars($row['id'])?></td></tr>
    <tr><th>GRN ID</th><td><?=htmlspecialchars($row['grn_id'])?> (<?=htmlspecialchars($row['grn_no'] ?? '')?>)</td></tr>
    <tr><th>PO No</th><td><?=htmlspecialchars($row['po_no'] ?? '')?></td></tr>
    <tr><th>Sent At</th><td><?=htmlspecialchars($row['sent_at'])?></td></tr>
    <tr><th>Status</th><td><?=htmlspecialchars($row['status'])?></td></tr>
    <tr><th>HTTP Code</th><td><?=htmlspecialchars($row['http_code'])?></td></tr>
    <tr><th>Attempts</th><td><?=htmlspecialchars($row['attempts'])?></td></tr>
    <tr><th>Response</th><td><pre style="white-space:pre-wrap;max-height:300px;overflow:auto;"><?=htmlspecialchars($row['response'] ?? '')?></pre></td></tr>
  </table>
  <div style="margin-top:10px">
    <form method="post" action="<?=$base?>/billing/retry">
      <input type="hidden" name="id" value="<?=htmlspecialchars($row['id'])?>">
      <button class="btn">Retry Delivery</button>
      <a class="btn" href="<?=$base?>/billing">Back</a>
    </form>
  </div>
</div>
