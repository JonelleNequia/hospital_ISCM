<?php $base=rtrim($app->config['app']['base_url'],'/'); ?>
<h1>Stock Adjustment</h1>
<form method="post" action="<?=$base?>/adjustments/store">
  <input type="hidden" name="csrf" value="<?=$app->csrfToken()?>">
  <div class="card">
    <label>Lot ID</label>
    <input class="input" type="number" name="lot_id" min="1" required>
    <label>Delta (±)</label>
    <input class="input" type="number" name="delta" required placeholder="+10 or -5">
    <label>Reason</label>
    <input class="input" name="reason" placeholder="e.g., COUNT_CORRECTION">
  </div>
  <button class="btn" type="submit">Apply</button>
</form>
