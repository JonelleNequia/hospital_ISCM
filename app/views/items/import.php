<?php
/** @var App $app */
$base = $app->baseUrl();
?>
<h1>Import Items (CSV)</h1>
<div class="card">
  <p>CSV columns supported (header row required): <strong>sku,name,category,uom,min_stock,max_stock,reorder_point,active,is_controlled,expiry_required,lot_tracking</strong></p>
  <form method="post" action="<?=$base?>/items/import" enctype="multipart/form-data">
    <?php echo $app->csrfField(); ?>
    <div style="display:flex;gap:12px;align-items:center">
      <input type="file" name="csv_file" accept="text/csv" required />
      <button class="btn" type="submit">Upload & Import</button>
      <a class="btn secondary" href="<?=$base?>/items">Back to Items</a>
    </div>
  </form>
</div>

<p>Tip: create a CSV from Excel/Workbench with the header row above. The importer does a best-effort insert and will skip rows on error.</p>
