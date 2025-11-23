<?php $base=rtrim($app->config['app']['base_url'],'/'); ?>
<h1>Shipment Events — #<?=$header['id']?> (PO: <?=htmlspecialchars($header['po_no'])?>)</h1>
<div class="card">
  <table class="table">
    <thead><tr><th>Time</th><th>Status</th><th>Remarks</th></tr></thead>
    <tbody>
      <?php foreach($events as $e): ?>
      <tr>
        <td><?=htmlspecialchars($e['event_time'])?></td>
        <td><?=htmlspecialchars($e['status'])?></td>
        <td><?=htmlspecialchars($e['remarks'])?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<h3>Add Event</h3>
<form method="post" action="<?=$base?>/shipments/event_store">
  <input type="hidden" name="csrf" value="<?=$app->csrfToken()?>">
  <input type="hidden" name="shipment_id" value="<?=$header['id']?>">
  <div class="card">
    <label>Status</label>
    <select name="status" required>
      <option value="IN_TRANSIT">IN_TRANSIT</option>
      <option value="OUT_FOR_DELIVERY">OUT_FOR_DELIVERY</option>
      <option value="DELAYED">DELAYED</option>
      <option value="DELIVERED">DELIVERED</option>
      <option value="CANCELLED">CANCELLED</option>
    </select>
    <label>Event Time</label>
    <input class="input" type="datetime-local" name="event_time">
    <label>Remarks</label>
    <input class="input" name="remarks" placeholder="Notes">
  </div>
  <button class="btn" type="submit">Add Event</button>
</form>
