<?php $base=rtrim($app->config['app']['base_url'],'/'); ?>
<h1>Issues</h1>
<div class="card">
  <div class="searchbar">
    <div style="margin-left:auto">
      <a class="btn" href="<?=$base?>/issues/create">New Issue</a>
    </div>
  </div>
  <table class="table">
    <thead><tr><th>Issue #</th><th>Requisition</th><th>Issued By</th><th>Date</th></tr></thead>
    <tbody>
      <?php foreach($issues as $x): ?>
      <tr>
        <td><?=htmlspecialchars($x['issue_no'])?></td>
        <td><?=htmlspecialchars($x['requisition_id'])?></td>
        <td><?=htmlspecialchars($x['issued_by'])?></td>
        <td><?=htmlspecialchars($x['issued_at'])?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
