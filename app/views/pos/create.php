<?php /** @var App $app */ $base = rtrim($app->config['app']['base_url'], '/'); ?>
<h1>New Purchase Order</h1>

<div class="card">
  <form method="post" action="<?=$base?>/pos/store" class="form">
    <input type="hidden" name="csrf" value="<?=$app->csrfToken()?>">
    <input type="hidden" name="nonce" value="<?=$nonce?>">

    <div class="grid cols-2">
      <div>
        <div class="label">PO #</div>
        <input class="input" name="po_no" placeholder="Auto or manual" />
      </div>
      <div>
        <div class="label">Supplier</div>
        <select class="input" name="supplier_id" required>
          <option value="">-- Select --</option>
          <?php foreach (($suppliers ?? []) as $s): ?>
            <option value="<?=$s['id']?>"><?=htmlspecialchars($s['name'])?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <div class="label">Expected Date</div>
        <input class="input" type="date" name="expected_date">
      </div>
      <div>
        <div class="label">Notes</div>
        <input class="input" name="notes" placeholder="Optional notes">
      </div>
    </div>

    <h3 style="margin-top:1rem">Lines</h3>
    <div class="grid cols-3" id="po-lines">
      <div class="row">
        <select class="input" name="lines[0][item_id]" required>
          <option value="">-- Item --</option>
          <?php foreach (($items ?? []) as $it): ?>
            <option value="<?=$it['id']?>"><?=htmlspecialchars($it['sku'].' — '.$it['name'])?></option>
          <?php endforeach; ?>
        </select>
        <input class="input" type="number" name="lines[0][qty]" placeholder="Qty" value="1" min="1" required>
        <input class="input" type="number" step="0.01" name="lines[0][price]" placeholder="Unit Price" value="0" min="0" required>
      </div>
    </div>

    <div style="margin-top:1rem"><button class="btn">Save PO</button></div>
  </form>
  <script>
function lockPOForm(form){
  // Disable all submit buttons on first submit to avoid double-click
  const btns = form.querySelectorAll('button[type=submit], .btn[type=submit], button.btn');
  btns.forEach(b => { b.disabled = true; b.textContent = 'Saving...'; });
  return true;
}
</script>
</div>
