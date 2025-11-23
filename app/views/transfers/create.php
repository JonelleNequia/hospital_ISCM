<?php $base=rtrim($app->config['app']['base_url'],'/'); ?>
<h1>Transfer Stock</h1>
<form method="post" action="<?=$base?>/transfers/store">
  <input type="hidden" name="csrf" value="<?=$app->csrfToken()?>">
  <div class="card">
    <label>From Location</label>
    <select name="from_location_id" required>
      <option value="">-- select --</option>
      <?php foreach($locations as $L): ?>
        <option value="<?=$L['id']?>"><?=htmlspecialchars($L['name'])?> (<?=htmlspecialchars($L['code'])?>)</option>
      <?php endforeach; ?>
    </select>

    <label>To Location</label>
    <select name="to_location_id" required>
      <option value="">-- select --</option>
      <?php foreach($locations as $L): ?>
        <option value="<?=$L['id']?>"><?=htmlspecialchars($L['name'])?> (<?=htmlspecialchars($L['code'])?>)</option>
      <?php endforeach; ?>
    </select>

    <label>Lot ID</label>
    <input class="input" type="number" name="lot_id" min="1" required>
    <label>Qty</label>
    <input class="input" type="number" name="qty" min="1" required>
  </div>
  <button class="btn" type="submit">Transfer</button>
</form>
