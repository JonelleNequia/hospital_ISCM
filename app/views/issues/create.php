<?php $base=rtrim($app->config['app']['base_url'],'/'); ?>
<h1>New Issue</h1>
<form method="post" action="<?=$base?>/issues/store">
  <input type="hidden" name="csrf" value="<?=$app->csrfToken()?>">

  <div class="card">
    <label>Requisition</label>
    <select name="requisition_id" onchange="window.location='<?=$base?>/issues/create?requisition_id='+this.value" required>
      <option value="">-- select --</option>
      <?php foreach($requisitions as $r): ?>
        <option value="<?=$r['id']?>" <?=$req_id==$r['id']?'selected':''?>><?=htmlspecialchars($r['req_no'])?> (<?=htmlspecialchars($r['dept'])?>)</option>
      <?php endforeach; ?>
    </select>

    <label>Issue From Location</label>
    <select name="location_id" id="loc" required>
      <option value="">-- select --</option>
      <?php foreach($locations as $L): ?>
        <option value="<?=$L['id']?>"><?=htmlspecialchars($L['name'])?> (<?=htmlspecialchars($L['code'])?>)</option>
      <?php endforeach; ?>
    </select>
  </div>

  <?php if($req_id && $header): ?>
  <div class="card">
    <h3>Lines (Request vs Issue)</h3>
    <table class="table">
      <thead><tr><th>Item</th><th>Requested</th><th>Issued</th><th>Lot ID</th><th>Qty to Issue</th></tr></thead>
      <tbody id="lines">
        <?php foreach($lines as $ln): 
          $rem = (int)$ln['qty_requested'] - (int)$ln['qty_issued']; if($rem<1) continue; ?>
        <tr>
          <td><?=htmlspecialchars($ln['item_name'])?> (<?=$ln['uom']?>)
            <input type="hidden" name="issue[item_id][]" value="<?=$ln['item_id']?>">
          </td>
          <td><?=$ln['qty_requested']?></td>
          <td><?=$ln['qty_issued']?></td>
          <td><input type="number" name="issue[lot_id][]" placeholder="Lot ID" min="1"></td>
          <td><input type="number" name="issue[qty][]" placeholder="Qty" min="1" max="<?=$rem?>"></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p class="muted">Tip: Hanapin ang Lot ID sa Stock list page mo o i-augment natin ito later ng lot dropdown per item+location.</p>
  </div>
  <?php endif; ?>

  <button class="btn" type="submit">Post Issue</button>
</form>
