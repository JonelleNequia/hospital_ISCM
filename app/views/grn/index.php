<?php /** @var App $app */ $base = $app->baseUrl(); ?>
<h1>Receiving (GRN)</h1>
<div class="card">
  <div class="searchbar">
    <div style="margin-left:auto">
      <a class="btn" href="<?=$base?>/grn/create">New GRN</a>
    </div>
  </div>
  <table class="table">
    <thead><tr><th>grn_no</th><th>po_no</th><th>received_at</th><th>status</th><th>actions</th></tr></thead>
    <tbody>
    <?php if (empty($rows)): ?>
      <tr><td colspan="5">No GRNs yet. Click “New GRN” to post a receipt.</td></tr>
    <?php else: foreach ($rows as $row): ?>
      <tr>
        <td><?=htmlspecialchars($row['grn_no'] ?? '')?></td>
        <td><?=htmlspecialchars($row['po_no'] ?? '')?></td>
        <td><?=htmlspecialchars($row['received_at'] ?? '')?></td>
        <td><?=htmlspecialchars($row['status'] ?? '')?></td>
        <td>
          <?php $gid = (int)($row['id'] ?? 0); ?>
          <button class="btn send-billing" data-grn-id="<?=$gid?>">Send to Billing</button>
          <span class="send-status" data-grn-id="<?=$gid?>" style="margin-left:8px"></span>
        </td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<script>
document.addEventListener('click', function(e){
  if (!e.target.matches('.send-billing')) return;
  var btn = e.target;
  var grnId = btn.getAttribute('data-grn-id');
  var statusEl = document.querySelector('.send-status[data-grn-id="'+grnId+'"]');
  btn.disabled = true; btn.textContent = 'Sending...';
  statusEl.textContent = '';
  fetch('<?=$base?>/dev/send_billing_report?grn_id='+encodeURIComponent(grnId), { credentials: 'same-origin' })
    .then(function(resp){ return resp.json().catch(function(){ return resp.text(); }); })
    .then(function(data){
      btn.disabled = false; btn.textContent = 'Send to Billing';
      if (typeof data === 'object' && data.ok) {
        statusEl.textContent = '✓ sent';
        statusEl.style.color = '#2a7f55';
      } else if (typeof data === 'object' && data.result) {
        // legacy response
        statusEl.textContent = data.result.http_code === 200 ? '✓ sent' : '✖ failed';
      } else if (typeof data === 'string') {
        statusEl.textContent = data;
      } else {
        statusEl.textContent = 'Unexpected response';
      }
    })
    .catch(function(err){
      btn.disabled = false; btn.textContent = 'Send to Billing';
      statusEl.textContent = 'Error';
      statusEl.style.color = '#c0392b';
      console.error(err);
    });
});
</script>
