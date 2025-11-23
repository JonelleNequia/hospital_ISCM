<?php
/** @var App $app */
$base = rtrim($app->config['app']['base_url'],'/');
$qVal = isset($q) ? $q : ($_GET['q'] ?? '');
?>
<h1>Requisitions</h1>
<div class="card">
  <div class="searchbar">
    <form method="get" action="<?=$base?>/requisitions">
      <input class="input" type="text" name="q" placeholder="Search..." value="<?=htmlspecialchars($qVal)?>">
      <button class="btn" type="submit">Search</button>
    </form>
    <div style="margin-left:auto">
      <a class="btn" href="<?=$base?>/requisitions/create">New Requisition</a>
    </div>
  </div>

  <table class="table">
    <thead><tr><th>Req No</th><th>Department</th><th>Status</th><th>Priority</th></tr></thead>
    <tbody>
    <?php foreach (($rows ?? []) as $row): ?>
      <tr>
        <td><?=htmlspecialchars($row['req_no'] ?? '')?></td>
        <td><?=htmlspecialchars($row['department'] ?? '')?></td>
        <td><?=htmlspecialchars($row['status'] ?? '')?></td>
        <td><?=htmlspecialchars($row['priority'] ?? '')?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
