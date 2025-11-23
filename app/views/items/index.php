<?php
/** @var App $app */
$base = $app->baseUrl();
$qVal = isset($q) ? $q : ($_GET['q'] ?? '');
$itemsForTable = $rows ?? ($items ?? []);
?>
<h1>Items</h1>

<div class="card">
  <div class="searchbar">
    <form method="get" action="<?=$base?>/items">
      <input class="input" type="text" name="q" placeholder="Search..." value="<?=htmlspecialchars($qVal)?>">
      <button class="btn" type="submit">Search</button>
    </form>
    <div style="margin-left:auto">
      <a class="btn" href="<?=$base?>/items/create">New Item</a>
      <a class="btn" href="<?=$base?>/items/import">Import CSV</a>
    </div>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>SKU</th>
        <th>Name</th>
        <th>Category</th>
        <th>UOM</th>
        <th>Reorder Point</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($itemsForTable as $row): ?>
      <tr>
        <td><?=htmlspecialchars($row['sku'] ?? '')?></td>
        <td><?=htmlspecialchars($row['name'] ?? '')?></td>
        <td><?=htmlspecialchars($row['category'] ?? '')?></td>
        <td><?=htmlspecialchars($row['uom'] ?? '')?></td>
        <td><?=htmlspecialchars($row['reorder_point'] ?? '')?></td>
        <td>
          <a class="btn" href="<?=$base?>/items/edit?id=<?=urlencode((string)($row['id'] ?? ''))?>">Edit</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
