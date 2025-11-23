<?php $base=rtrim($app->config['app']['base_url'],'/'); ?>
<h1>New Shipment</h1>
<form method="post" action="<?=$base?>/shipments/store">
  <input type="hidden" name="csrf" value="<?=$app->csrfToken()?>">
  <div class="card">
    <label>PO</label>
    <select name="po_id" required>
      <option value="">-- select --</option>
      <?php foreach($pos as $p): ?>
        <option value="<?=$p['id']?>"><?=htmlspecialchars($p['po_no'])?> (<?=htmlspecialchars($p['status'])?>)</option>
      <?php endforeach; ?>
    </select>
    <label>Carrier</label>
    <input class="input" name="carrier" placeholder="Carrier">
    <label>Tracking No</label>
    <input class="input" name="tracking_no" placeholder="Tracking number">
    <label>ETA</label>
    <input class="input" type="datetime-local" name="eta">
  </div>
  <button class="btn" type="submit">Create Shipment</button>
</form>
