<?php /** @var App $app */ $base = $app->baseUrl(); ?>
<h1>Create GRN</h1>

<div class="card">
  <form method="post" action="<?=$base?>/grn/store" class="form">
    <input type="hidden" name="csrf" value="<?=$app->csrfToken()?>">

    <div class="grid cols-3">
      <div>
        <div class="label">PO</div>
        <select class="input" name="po_id" onchange="location='<?=$base?>/grn/create?po_id='+this.value">
          <option value="">-- Select PO --</option>
          <?php foreach (($pos ?? []) as $p): ?>
            <option value="<?=$p['id']?>" <?= (isset($po['id']) && (int)$po['id']===(int)$p['id']) ? 'selected' : '' ?>>
              <?=htmlspecialchars($p['po_no'])?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <div class="label">Receive To (Location)</div>
        <select class="input" name="location_id" required>
          <?php foreach (($loc ?? []) as $l): ?>
            <option value="<?=$l['id']?>"><?=htmlspecialchars($l['name'])?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <div class="label">Remarks</div>
        <input class="input" name="remarks" placeholder="Optional">
      </div>
    </div>

    <?php if (!empty($lines)): ?>
      <h3 style="margin-top:1rem">Lines</h3>
      <table class="table">
        <thead>
          <tr>
            <th>Item</th>
            <th>Ordered</th>
            <th>Already Received</th>
            <th>Receive Now</th>
            <th>Lot No</th>
            <th>Expiry</th>
          </tr>
        </thead>
        <tbody>
  <?php foreach ($lines as $i => $ln):
  $ordered  = (int)($ln['qty_ordered'] ?? 0);
  $received = (int)($ln['qty_received'] ?? 0);
  $out      = max(0, $ordered - $received);
?>
  <tr>
    <td>
      <?=htmlspecialchars(($ln['sku'] ?? '').' — '.($ln['name'] ?? ''))?>
      <input type="hidden" name="lines[<?=$i?>][po_item_id]" value="<?= (int)($ln['id'] ?? 0) ?>">
      <input type="hidden" name="lines[<?=$i?>][item_id]"    value="<?= (int)($ln['item_id'] ?? 0) ?>">
      <input type="hidden" name="lines[<?=$i?>][present]"    value="1">
    </td>
    <td><?= $ordered ?></td>
    <td><?= $received ?></td>

    <!-- Accept (good) qty -->
    <td>
      <input class="input" type="number" min="0" step="1"
             name="lines[<?=$i?>][qty_received]"
             value="<?= $out ?>">
    </td>

    <!-- Reject qty -->
    <td>
      <input class="input" type="number" min="0" step="1"
             name="lines[<?=$i?>][qty_rejected]" value="0">
    </td>

    <!-- Reason -->
    <td>
      <select class="input" name="lines[<?=$i?>][reject_reason]">
        <option value="">--</option>
        <option value="DAMAGED">Damaged</option>
        <option value="EXPIRED">Expired</option>
        <option value="SHORT">Short</option>
        <option value="OVER">Over</option>
        <option value="WRONG_ITEM">Wrong Item</option>
        <option value="OTHER">Other</option>
      </select>
    </td>

    <!-- Disposition -->
    <td>
      <select class="input" name="lines[<?=$i?>][reject_disposition]">
        <option value="">--</option>
        <option value="RETURN_TO_SUPPLIER">Return to Supplier</option>
        <option value="DISCARD">Discard</option>
        <option value="QUARANTINE">Quarantine</option>
      </select>
    </td>

    <!-- Lot / Expiry (retain your existing inputs) -->
    <td><input class="input" name="lines[<?=$i?>][lot_no]" placeholder="Lot"></td>
    <td><input class="input" type="date" name="lines[<?=$i?>][expiry_date]"></td>
  </tr>
<?php endforeach; ?>
</tbody>
      </table>
    <?php else: ?>
      <p>Pumili ng PO para lumabas ang lines.</p>
    <?php endif; ?>

    <div style="margin-top:1rem">
      <button class="btn">Post GRN</button>
    </div>
  </form>
</div>