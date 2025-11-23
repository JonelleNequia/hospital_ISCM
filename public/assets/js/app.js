document.addEventListener('DOMContentLoaded',()=>{
  const flashes = document.querySelectorAll('.flash');
  flashes.forEach(f => setTimeout(()=> f.remove(), 5000));
});

// Utility: open print dialog with small delay for any dynamic content
function printPage() {
  setTimeout(()=> window.print(), 120);
}

// Expose to global for inline buttons
window.printPage = printPage;

// Toggle sidebar collapsed state
function toggleSidebar() {
  const layout = document.querySelector('.layout');
  if (!layout) return;
  layout.classList.toggle('sidebar-collapsed');
  console.info('toggleSidebar: sidebar-collapsed=', layout.classList.contains('sidebar-collapsed'));
}

// Export first table in .content to CSV
function exportTableCSV(filename) {
  const container = document.querySelector('.content .container');
  if (!container) return alert('No content container found');
  const table = container.querySelector('table');
  if (!table) return alert('No table found on this page to export');
  const rows = Array.from(table.querySelectorAll('tr'));
  const csv = rows.map(tr => {
    const cols = Array.from(tr.querySelectorAll('th,td'));
    return cols.map(td => '"' + (td.innerText || '').replace(/"/g,'""') + '"').join(',');
  }).join('\n');
  const blob = new Blob([csv], {type: 'text/csv;charset=utf-8;'});
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename || 'export.csv';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

window.toggleSidebar = toggleSidebar;
window.exportTableCSV = exportTableCSV;

// Wire sidebar toggle button when present
// Log to confirm script loaded
console.info('app.js loaded');

document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('sidebarToggle');
  if (btn) btn.addEventListener('click', () => { toggleSidebar(); });

  // Attach toolbar listeners (avoid inline onclick)
  const pBtn = document.getElementById('pagePrintBtn');
  if (pBtn) pBtn.addEventListener('click', () => printPage());
  const eBtn = document.getElementById('pageExportBtn');
  if (eBtn) eBtn.addEventListener('click', () => exportTableCSV());
});

// Nav group toggles (collapsible sidebar sections)
document.addEventListener('DOMContentLoaded', () => {
  const groups = document.querySelectorAll('.nav-group');
  groups.forEach(g => {
    const btn = g.querySelector('.group-btn');
    const sub = g.querySelector('.nav-sub');
    if (!btn || !sub) return;
    // Initialize collapsed unless active link inside
    if (g.querySelector('.active')) g.classList.add('open');
    btn.addEventListener('click', () => {
      g.classList.toggle('open');
    });
  });
});

// Robust delegated click handler as a fallback / enhancement.
document.addEventListener('click', function (ev) {
  try {
    const t = ev.target;
    // Sidebar toggle (handles clicks on inner elements too)
    const st = t.closest && t.closest('#sidebarToggle');
    if (st) {
      toggleSidebar();
      return;
    }

    // Group button clicks
    const gb = t.closest && t.closest('.group-btn');
    if (gb) {
      const group = gb.closest('.nav-group');
      if (group) {
        group.classList.toggle('open');
        // set aria-expanded for accessibility
        const expanded = group.classList.contains('open');
        try { gb.setAttribute('aria-expanded', expanded ? 'true' : 'false'); } catch (e) {}
      }
      return;
    }
  } catch (err) {
    console && console.error && console.error('delegated click handler error', err);
  }
});
