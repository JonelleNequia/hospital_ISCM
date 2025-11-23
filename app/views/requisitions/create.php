<?php /** @var App $app */ $base = $app->baseUrl(); ?>
<h1>New Requisition</h1>

<div class="card">
  <form method="post" action="<?=$base?>/requisitions/store" class="form">
    <input type="hidden" name="csrf" value="<?=$app->csrfToken()?>">

    <div class="grid cols-3">
      <div>
        <div class="label">Department *</div>
        <select class="input" name="department_id" required>
          <option value="">-- Select Department --</option>
          <?php foreach (($departments ?? []) as $d): ?>
            <option value="<?=$d['id']?>"><?=htmlspecialchars(($d['code'] ?? '').' — '.$d['name'])?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <div class="label">Priority</div>
        <select class="input" name="priority">
          <option value="NORMAL">Normal</option>
          <option value="HIGH">High</option>
          <option value="LOW">Low</option>
        </select>
      </div>

      <div>
        <div class="label">Remarks</div>
        <input class="input" name="remarks" placeholder="Optional">
      </div>
    </div>

    <h3 style="margin-top:1rem">Lines</h3>
    <table class="table" id="req-lines">
      <thead>
        <tr>
          <th style="width:50%">Item</th>
          <th style="width:20%">Qty Requested</th>
          <th style="width:10%"></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <select class="input" name="lines[0][item_id]" required>
              <option value="">-- Select Item --</option>
              <?php foreach (($items ?? []) as $it): ?>
                <option value="<?=$it['id']?>"><?=htmlspecialchars(($it['sku'] ?? '').' — '.$it['name'])?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td><input class="input" type="number" name="lines[0][qty_requested]" min="1" step="1" value="1" required></td>
          <td><button type="button" class="btn" onclick="addRow()">+</button></td>
        </tr>
      </tbody>
    </table>

    <div style="margin-top:1rem">
      <button class="btn" type="submit">Submit Requisition</button>
      <a class="btn" href="<?=$base?>/requisitions" style="background:#777">Cancel</a>
    </div>
  </form>
</div>

<script>
let rowIdx = 1;
function addRow(){
  const tbody = document.querySelector('#req-lines tbody');
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>
      <select class="input" name="lines[${rowIdx}][item_id]" required>
        <option value="">-- Select Item --</option>
        <?php foreach (($items ?? []) as $it): ?>
          <option value="<?=$it['id']?>"><?=htmlspecialchars(($it['sku'] ?? '').' — '.$it['name'])?></option>
        <?php endforeach; ?>
      </select>
    </td>
    <td><input class="input" type="number" name="lines[${rowIdx}][qty_requested]" min="1" step="1" value="1" required></td>
    <td><button type="button" class="btn" onclick="this.closest('tr').remove()">−</button></td>
  `;
  tbody.appendChild(tr);
  rowIdx++;
}
</script>