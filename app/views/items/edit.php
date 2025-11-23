<?php $base = $app->baseUrl(); ?>
<h1>Edit Item</h1>
<div class="card">
  <form method="post" action="<?=$base?>/items/update" class="form">
    <input type="hidden" name="csrf" value="<?=$app->csrfToken()?>">
    <input type="hidden" name="id" value="<?=$item['id']?>">

    <div class="grid cols-3">
      <div><div class="label">SKU</div><input class="input" name="sku" value="<?=htmlspecialchars($item['sku'])?>" required></div>
      <div><div class="label">Name</div><input class="input" name="name" value="<?=htmlspecialchars($item['name'])?>" required></div>
      <div><div class="label">Category</div><input class="input" name="category" value="<?=htmlspecialchars($item['category'])?>"></div>
      <div><div class="label">UOM</div><input class="input" name="uom" value="<?=htmlspecialchars($item['uom'])?>"></div>
      <div><div class="label">Reorder Point</div><input class="input" type="number" name="reorder_point" value="<?=$item['reorder_point']?>"></div>
      <div><div class="label">Lead Time (days)</div><input class="input" type="number" name="lead_time_days" value="<?=$item['lead_time_days']?>"></div>

      <div><label><input type="checkbox" name="is_controlled"   <?=$item['is_controlled']?'checked':''?>> Controlled</label></div>
      <div><label><input type="checkbox" name="expiry_required" <?=$item['expiry_required']?'checked':''?>> Expiry Required</label></div>
      <div><label><input type="checkbox" name="lot_tracking"    <?=$item['lot_tracking']?'checked':''?>> Lot Tracking</label></div>

      <div><div class="label">Min Stock</div><input class="input" type="number" name="min_stock" value="<?=$item['min_stock']?>"></div>
      <div><div class="label">Max Stock</div><input class="input" type="number" name="max_stock" value="<?=$item['max_stock']?>"></div>
      <div><label><input type="checkbox" name="active" <?=$item['active']?'checked':''?>> Active</label></div>
    </div>

    <div><button class="btn">Update Item</button></div>
  </form>
</div>
