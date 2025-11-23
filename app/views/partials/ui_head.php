<?php /** @var App $app */ $base = $app->baseUrl(); ?>
<link rel="stylesheet" href="<?=$base?>/assets/css/ui-normalize.css">
<link rel="stylesheet" href="<?=$base?>/assets/css/ui-layout.css">
<link rel="stylesheet" href="<?=$base?>/assets/css/ui-layout-fix.css">
<link rel="stylesheet" href="<?=$base?>/assets/css/print.css" media="print">
<?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isLogin = (substr($path, -5) === 'login' || preg_match('~/login$~', $path));
$noSidebar = !empty($GLOBALS['layout_no_sidebar']) || $isLogin;

// helper to mark nav active
$active = function($needle) use ($path, $base) {
  $p = $path;
  if ($base && str_starts_with($p, rtrim($base,'/'))) {
    $p = substr($p, strlen(rtrim($base,'/')));
  }
  $p = ltrim($p,'/');
  $n = ltrim($needle,'/');
  return ($n === '' ? ($p === '' || $p === '/') : str_starts_with($p, $n)) ? 'active' : '';
};
?>
<div class="layout">
  <?php if (!$noSidebar): ?>
  <aside class="sidebar">
    <div class="brand">
      <img src="<?=$base?>/assets/img/logo.png" alt="Logo" onerror="this.style.display='none'">
      <div class="title">EVERGREEN MEDICAL HOSPITAL</div>
        <button id="sidebarToggle" class="btn small" type="button" aria-label="Toggle sidebar">☰</button>
    </div>
    <nav class="nav">
      <?php
        $role = $_SESSION['user']['role_name'] ?? null;

        // helper to check role permission
        $can = function(array $allowed) use ($role) {
          if ($role === 'Admin') return true;
          return in_array($role, $allowed, true);
        };
      ?>

      <a class="<?=$active('')?>" href="<?=$base?>/" data-label="Dashboard" title="Dashboard">Dashboard</a>

      <div class="nav-group">
        <button type="button" class="group-btn">Master ▾</button>
        <div class="nav-sub">
          <?php if ($can(['InventoryClerk','Procurement','Pharmacy'])): ?>
            <a class="<?=$active('items')?>" href="<?=$base?>/items" data-label="Items" title="Items">Items</a>
          <?php endif; ?>
          <?php if ($can(['Procurement','Admin'])): ?>
            <a class="<?=$active('suppliers')?>" href="<?=$base?>/suppliers" data-label="Suppliers" title="Suppliers">Suppliers</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="nav-group">
        <button type="button" class="group-btn">Purchasing ▾</button>
        <div class="nav-sub">
          <?php if ($can(['Procurement'])): ?>
            <a class="<?=$active('pos')?>" href="<?=$base?>/pos" data-label="Purchase Orders" title="Purchase Orders">Purchase Orders</a>
            <a class="<?=$active('shipments')?>" href="<?=$base?>/shipments" data-label="Shipments" title="Shipments">Shipments</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="nav-group">
        <button type="button" class="group-btn">Receiving & Stock ▾</button>
        <div class="nav-sub">
          <?php if ($can(['Receiver'])): ?>
            <a class="<?=$active('grn')?>" href="<?=$base?>/grn" data-label="GRN" title="GRN">GRN</a>
          <?php endif; ?>
          <?php if ($can(['InventoryClerk','Pharmacy'])): ?>
            <a class="<?=$active('inventory')?>" href="<?=$base?>/inventory" data-label="Inventory" title="Inventory">Inventory</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="nav-group">
        <button type="button" class="group-btn">Departments ▾</button>
        <div class="nav-sub">
          <?php if ($can(['DeptReq','Pharmacy'])): ?>
            <a class="<?=$active('requisitions')?>" href="<?=$base?>/requisitions" data-label="Requisitions" title="Requisitions">Requisitions</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="nav-group">
        <button type="button" class="group-btn">Billing ▾</button>
        <div class="nav-sub">
          <?php if ($can(['Procurement','Receiver','Admin'])): ?>
            <a class="<?=$active('billing')?>" href="<?=$base?>/billing" data-label="Invoice Dashboard" title="Invoice Dashboard">Invoice Dashboard</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="nav-group">
        <button type="button" class="group-btn">User ▾</button>
        <div class="nav-sub">
          <?php if ($role): ?>
            <a class="<?=$active('account/change_password')?>" href="<?=$base?>/account/change_password" data-label="Change Password" title="Change Password">Change Password</a>
            <a href="<?=$base?>/logout" data-label="Logout" title="Logout">Logout</a>
          <?php endif; ?>
          <?php if ($can(['Admin'])): ?>
            <a class="<?=$active('users')?>" href="<?=$base?>/users" data-label="Manage Users" title="Manage Users">Manage Users</a>
          <?php endif; ?>
        </div>
      </div>
    </nav>
  </aside>
  <?php endif; ?>
  <div class="content">
    <div class="container">
      <?php if (!$noSidebar): ?>
        <div class="page-toolbar">
          <button id="pagePrintBtn" class="btn" type="button">Print</button>
          <button id="pageExportBtn" class="btn" type="button">Export CSV</button>
        </div>
      <?php endif; ?>
        <script>
          // Fallback bindings in case app.js did not load or listeners not attached.
          (function(){
            try {
              var layout = document.querySelector('.layout');
              var st = document.getElementById('sidebarToggle');
              if (st && layout && !st.dataset.bound) {
                st.addEventListener('click', function(){ layout.classList.toggle('sidebar-collapsed'); });
                st.dataset.bound = '1';
              }

              // nav-group toggles
              var groups = document.querySelectorAll('.nav-group');
              groups.forEach(function(g){
                var btn = g.querySelector('.group-btn');
                var sub = g.querySelector('.nav-sub');
                if (!btn || !sub) return;
                if (!btn.dataset.bound) {
                  if (g.querySelector('.active')) g.classList.add('open');
                  btn.addEventListener('click', function(){ g.classList.toggle('open'); });
                  btn.dataset.bound = '1';
                }
              });

              // print/export toolbar fallback
              var p = document.getElementById('pagePrintBtn');
              if (p && !p.dataset.bound) {
                p.addEventListener('click', function(){ setTimeout(function(){ window.print(); }, 120); });
                p.dataset.bound = '1';
              }
              var e = document.getElementById('pageExportBtn');
              if (e && !e.dataset.bound) {
                e.addEventListener('click', function(){
                  var container = document.querySelector('.content .container');
                  if (!container) return alert('No content container found');
                  var table = container.querySelector('table');
                  if (!table) return alert('No table found on this page to export');
                  var rows = Array.from(table.querySelectorAll('tr'));
                  var csv = rows.map(function(tr){
                    var cols = Array.from(tr.querySelectorAll('th,td'));
                    return cols.map(function(td){ return '"' + (td.innerText || '').replace(/"/g,'""') + '"'; }).join(',');
                  }).join('\n');
                  var blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
                  var url = URL.createObjectURL(blob);
                  var a = document.createElement('a'); a.href = url; a.download = 'export.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
                });
                e.dataset.bound = '1';
              }
            } catch (err) {
              console && console.error && console.error('ui_head fallback error', err);
            }
          })();
        </script>
