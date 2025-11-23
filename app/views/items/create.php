<?php $base = $app->baseUrl(); ?>
<h1>New Item</h1>
<div class="card">
  <form method="post" action="<?=$base?>/items/store" class="form">
    <input type="hidden" name="csrf" value="<?=$app->csrfToken()?>">

    <div class="grid cols-3">
      <div><div class="label">SKU</div><input class="input" name="sku" required></div>
      <div><div class="label">Name</div><input class="input" name="name" required></div>
      <div><div class="label">Category</div><input class="input" name="category"></div>
      <div><div class="label">UOM</div><input class="input" name="uom" value="EA"></div>
      <div><div class="label">Reorder Point</div><input class="input" type="number" name="reorder_point" value="0"></div>
      <div><div class="label">Lead Time (days)</div><input class="input" type="number" name="lead_time_days" value="0"></div>
      <div><label><input type="checkbox" name="is_controlled"> Controlled</label></div>
      <div><label><input type="checkbox" name="expiry_required" checked> Expiry Required</label></div>
      <div><label><input type="checkbox" name="lot_tracking" checked> Lot Tracking</label></div>
      <div><div class="label">Min Stock</div><input class="input" type="number" name="min_stock" value="0"></div>
      <div><div class="label">Max Stock</div><input class="input" type="number" name="max_stock" value="0"></div>
      <div><label><input type="checkbox" name="active" checked> Active</label></div>
    </div>

    <div><button class="btn">Create Item</button></div>
  </form>
</div>
