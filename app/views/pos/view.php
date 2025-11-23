<h1>PO <?=htmlspecialchars($header['po_no'])?></h1>
<div class="card">
  <div><b>Supplier:</b> <?=htmlspecialchars($header['supplier'])?></div>
  <div><b>Status:</b> <span class="badge"><?=$header['status']?></span></div>
  <div><b>Expected:</b> <?=htmlspecialchars($header['expected_date'] ?? '')?></div>
</div>

<div class="card" style="margin-top:12px">
  <h2>Lines</h2>
  <table class="table">
    <thead><tr><th>SKU</th><th>Name</th><th>Qty Ordered</th><th>Qty Received</th><th>Unit Price</th></tr></thead>
    <tbody>
      <?php foreach($lines as $l): ?>
      <tr>
        <td><?=htmlspecialchars($l['sku'])?></td>
        <td><?=htmlspecialchars($l['name'])?></td>
        <td><?=$l['qty_ordered']?></td>
        <td><?=$l['qty_received']?></td>
        <td><?=number_format($l['unit_price'],2)?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div style="margin-top:12px">
  <a class="btn" href="grn/create?po_id=<?=$header['id']?>">Receive (Create GRN)</a>
</div>
