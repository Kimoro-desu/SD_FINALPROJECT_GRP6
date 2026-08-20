<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HariBorrow — System Logs</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
<script src="../js/api.js"></script>
<script src="../js/auth_guard.js?v=1778041298"></script>
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
  /* ── BASE STYLES ── */
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg-deep:      #030304;
    --glass:        rgba(15, 15, 20, 0.45);
    --glass-heavy:  rgba(10, 10, 13, 0.85);
    --glass-border: rgba(255, 255, 255, 0.08);
    --gold:         #E5C07B;
    --gold-light:   #FCEBAF;
    --gold-dark:    #A68A48;
    --gold-dim:     rgba(229, 192, 123, 0.1);
    --gold-glow:    rgba(229, 192, 123, 0.25);
    --text-1:       #FFFFFF;
    --text-2:       #A39E93;
    --text-3:       #6B665A;
    --sidebar-w:    260px;
    --info:         #60a5fa;
    --success:      #4ade80;
    --warning:      #fbbf24;
    --danger:       #ff6b7a;
  }

  html, body {
    height: 100vh; width: 100vw; overflow: hidden;
    font-family: 'Outfit', sans-serif;
    background-color: var(--bg-deep);
    color: var(--text-1);
    display: flex;
  }

  ::-webkit-scrollbar { width: 6px; background: transparent; }
  ::-webkit-scrollbar-thumb { background: var(--text-3); border-radius: 10px; }

  /* ── BACKGROUND ── */
  .bg-mesh {
    position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 0;
    background: 
      radial-gradient(circle at 10% 90%, rgba(229, 192, 123, 0.05), transparent 50%),
      radial-gradient(circle at 90% 10%, rgba(166, 138, 72, 0.05), transparent 50%),
      var(--bg-deep);
  }

  .ambient-glow {
    position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
    pointer-events: none;
    background: radial-gradient(480px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(229, 192, 123, 0.06), transparent 50%);
    z-index: 9999; mix-blend-mode: screen; transition: background 0.08s ease-out;
  }

  /* ── SIDEBAR ── */
  .sidebar {
    width: var(--sidebar-w); height: 100vh;
    background: var(--glass-heavy); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
    border-right: 1px solid var(--glass-border);
    display: flex; flex-direction: column; z-index: 100;
    position: relative; flex-shrink: 0;
  }

  .sidebar-header {
    padding: 32px 24px; border-bottom: 1px solid var(--glass-border);
    display: flex; align-items: center; gap: 12px;
  }
  
  .nav-logo { 
    height: 48px; width: auto; object-fit: contain;
    filter: drop-shadow(0 0 10px rgba(229, 192, 123, 0.4)) brightness(1.2) contrast(1.3) saturate(1.4);
  }

  .nav-title {
    font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 600;
    background: linear-gradient(135deg, #FFF 0%, var(--gold-light) 50%, var(--gold-dark) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 0.02em;
    line-height: 1;
  }
  
  .admin-badge {
    font-family: 'Outfit', sans-serif; font-size: 8px; font-weight: 600;
    letter-spacing: 0.2em; text-transform: uppercase; color: var(--gold);
    border: 1px solid var(--gold-dim); padding: 2px 6px; border-radius: 4px; margin-top: 4px; display: inline-block;
  }

  .nav-menu { padding: 24px 12px; display: flex; flex-direction: column; gap: 6px; flex-grow: 1; overflow-y: auto; }
  
  .nav-section-title {
    font-size: 10px; font-weight: 600; letter-spacing: 0.2em; text-transform: uppercase;
    color: var(--text-3); margin: 16px 0 8px 12px;
  }

  .nav-link {
    display: flex; align-items: center; gap: 12px; padding: 12px 16px;
    color: var(--text-2); text-decoration: none; border-radius: 8px;
    font-size: 14px; transition: all 0.3s; border: 1px solid transparent;
    position: relative;
  }
  .nav-link i { font-size: 20px; color: var(--text-3); transition: color 0.2s; }
  .nav-link:hover { background: rgba(255,255,255,0.03); color: var(--text-1); }
  .nav-link.active { background: var(--gold-dim); border-color: rgba(229, 192, 123, 0.2); color: var(--gold-light); }
  .nav-link.active i { color: var(--gold); }

  /* ── NOTIFICATION-STYLE HIGHLIGHT FOR PENDING ITEMS ── */
  .nav-link.has-notif {
    background: rgba(239, 68, 68, 0.06);
    border-color: rgba(239, 68, 68, 0.18);
    color: var(--text-1);
  }
  .nav-link.has-notif i { color: #f87171; }
  .nav-link.has-notif:hover {
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.3);
  }
  .nav-link.active.has-notif {
    background: linear-gradient(135deg, var(--gold-dim), rgba(239, 68, 68, 0.06));
    border-color: rgba(239, 68, 68, 0.25);
  }

  .nav-badge {
            margin-left: auto;
            background: var(--danger);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            min-width: 18px;
            text-align: center;
            animation: badgePulse 2s ease-in-out infinite;
            box-shadow: 0 0 8px rgba(239, 68, 68, 0.4);
        }

  @keyframes badgePulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 8px rgba(239, 68, 68, 0.4); }
    50% { transform: scale(1.1); box-shadow: 0 0 14px rgba(239, 68, 68, 0.6); }
  }

  .sidebar-footer { padding: 24px; border-top: 1px solid var(--glass-border); }
  .admin-profile { display: flex; align-items: center; gap: 12px; }
  .admin-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--gold-dark); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; color: var(--bg-deep); }
  .admin-info { display: flex; flex-direction: column; }
  .admin-name { font-size: 14px; font-weight: 500; color: var(--text-1); }
  .admin-role { font-size: 11px; color: var(--text-3); }

  /* ── MAIN CONTENT ── */
  .main-content {
    flex-grow: 1; height: 100vh; overflow: hidden; position: relative; z-index: 10;
    padding: 40px 48px; display: flex; flex-direction: column;
  }

  .header-area { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; }
  .page-title h1 { font-family: 'Cormorant Garamond', serif; font-size: 42px; font-weight: 400; color: var(--text-1); line-height: 1.1; margin-bottom: 8px; }
  .page-title p { font-size: 14px; color: var(--text-2); font-weight: 300; letter-spacing: 0.03em; }

  /* ── TOOLBAR ── */
  .toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
  .filters { display: flex; gap: 12px; flex: 1; }
  
  .search-wrap { position: relative; flex: 1; max-width: 360px; }
  .search-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-3); font-size: 18px; pointer-events: none; }
  .search-input { width: 100%; background: var(--glass); border: 1px solid var(--glass-border); border-radius: 10px; padding: 11px 16px 11px 42px; color: var(--text-1); font-family: 'Outfit', sans-serif; font-size: 13px; outline: none; transition: border-color 0.2s; }
  .search-input:focus { border-color: rgba(229,192,123,0.4); }

  .filter-select, .date-input { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 10px; padding: 11px 14px; color: var(--text-2); font-family: 'Outfit', sans-serif; font-size: 13px; outline: none; transition: border-color 0.2s; }
  .filter-select { padding-right: 36px; cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236B665A' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; }
  .filter-select:focus, .date-input:focus { border-color: var(--gold); color: var(--text-1); }
  
  .btn-outline { background: transparent; border: 1px solid var(--glass-border); color: var(--text-2); padding: 11px 16px; border-radius: 10px; font-family: 'Outfit', sans-serif; font-size: 13px; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
  .btn-outline:hover { border-color: var(--gold); color: var(--gold); background: var(--gold-dim); }

  /* ── DATA TABLE ── */
  .data-panel { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 12px; overflow-y: auto; flex: 1; min-height: 0; backdrop-filter: blur(16px); }
  
  table { width: 100%; border-collapse: collapse; }
  th { text-align: left; padding: 16px 20px; font-size: 10px; font-weight: 600; letter-spacing: 0.15em; text-transform: uppercase; color: var(--text-3); border-bottom: 1px solid var(--glass-border); background: rgba(15, 15, 20, 0.95); position: sticky; top: 0; z-index: 10; }
  td { padding: 16px 20px; font-size: 13px; color: var(--text-2); font-weight: 300; border-bottom: 1px solid rgba(255,255,255,0.03); vertical-align: middle; }
  tr:hover td { background: rgba(255,255,255,0.02); }
  
  .log-timestamp { display: block; color: var(--text-1); font-family: monospace; font-size: 12px; margin-bottom: 2px; }
  .log-date { font-size: 11px; color: var(--text-3); }
  
  .log-desc { color: var(--text-1); font-weight: 400; display: block; margin-bottom: 2px; }
  .log-actor { font-size: 11px; color: var(--gold); }

  /* ── EVENT TYPE PILLS ── */
  .type-pill { padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; border: 1px solid transparent; display: inline-block;}
  .type-auth { background: rgba(96, 165, 250, 0.1); color: var(--info); border-color: rgba(96, 165, 250, 0.2); }
  .type-txn { background: rgba(74, 222, 128, 0.1); color: var(--success); border-color: rgba(74, 222, 128, 0.2); }
  .type-admin { background: rgba(251, 191, 36, 0.1); color: var(--warning); border-color: rgba(251, 191, 36, 0.2); }
  .type-sec { background: rgba(255, 107, 122, 0.1); color: var(--danger); border-color: rgba(255, 107, 122, 0.2); }

  .filter-select option,
  .date-input option {
    background-color: #1a1a22;
    color: #e2ddd6;
    padding: 8px 12px;
    font-family: 'Outfit', sans-serif;
  }

  .filter-select option:checked {
    background-color: #2a2a35;
    color: var(--gold-light);
  }

</style>
  <link rel="stylesheet" href="../css/theme.css?v=1778041298">
</head>
<body>

<div class="bg-mesh"></div>
<div class="ambient-glow" id="glow"></div>

<aside class="sidebar">
  <div class="sidebar-header">
    <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="HariBorrow Logo" class="nav-logo">
    <div>
      <div class="nav-title">HariBorrow</div>
      <span class="admin-badge">Admin Console</span>
    </div>
  </div>

  <nav class="nav-menu">
    <a href="admin_dashboard.php" class="nav-link"><i class="ph ph-squares-four"></i> Command Center</a>
    <div class="nav-section-title">Operations</div>
      <a href="pendingrequest_approval.php" class="nav-link" id="pendingNavLink"><i class="ph ph-bell-ringing"></i> Pending Requests
        <span class="nav-badge" id="pendingNavBadge" style="display:none;">0</span>
      </a>
      <a href="active_transactions.php" class="nav-link"><i class="ph ph-clock-counter-clockwise"></i> All Transactions</a>
    
    <div class="nav-section-title">Database</div>
    <a href="asset_inventory.php" class="nav-link"><i class="ph ph-stack"></i> Asset Inventory</a>
    <a href="registered_users.php" class="nav-link"><i class="ph ph-users"></i> Registered Users</a>
    
    <div class="nav-section-title">System</div>
    <a href="registration_approval.php" class="nav-link"><i class="ph ph-shield-check"></i> Registration Approvals</a>
    <a href="system_logs.php" class="nav-link active"><i class="ph ph-file-text"></i> System Logs</a>
  </nav>

  <div class="sidebar-footer">
    <div class="admin-profile">
      <div class="admin-avatar" id="adminAvatar">SA</div>
      <div class="admin-info">
        <span class="admin-name" id="adminName">System Admin</span>
        <span class="admin-role" id="adminRole">Admin</span>
      </div>
      <a href="javascript:void(0)" onclick="window.api.removeToken(); window.location.href='login.php'"
        style="margin-left: auto; color: var(--text-3); cursor: pointer;">
        <i class="ph ph-sign-out" style="font-size: 20px;"></i>
      </a>
    </div>
  </div>
</aside>

<main class="main-content">
  
  <div class="header-area">
    <div class="page-title">
      <h1>System Logs</h1>
      <p>Immutable audit trail for security, authentication, and transaction monitoring.</p>
    </div>
    <button type="button" class="btn-outline" onclick="downloadCsv()"><i class="ph ph-download-simple"></i> Download CSV</button>
  </div>

  <div class="toolbar">
    <div class="filters">
      <div class="search-wrap">
        <i class="ph ph-magnifying-glass"></i>
        <input class="search-input" type="text" id="logSearch" placeholder="Search logs by keyword, actor, or IP...">
      </div>
      
      <select class="filter-select" id="eventType">
        <option value="">All Events</option>
        <option value="auth">Authentication</option>
        <option value="transaction">Transactions</option>
        <option value="admin">Admin Actions</option>
        <option value="security">Security Alerts</option>
      </select>

      <input type="date" class="date-input" id="startDate" title="Start Date">
      <input type="date" class="date-input" id="endDate" title="End Date">
      <button type="button" class="btn-outline" onclick="loadLogs()"><i class="ph ph-funnel"></i> Apply</button>
    </div>
  </div>

  <div class="data-panel">
    <table>
      <thead>
        <tr>
          <th>Timestamp</th>
          <th>Event Type</th>
          <th>Action Description</th>
          <th>IP Address</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody id="logsTbody"></tbody>
    </table>
  </div>
</main>

<script>

  const glow = document.getElementById('glow');
  document.addEventListener('mousemove', (e) => {
    glow.style.setProperty('--mouse-x', e.clientX + 'px');
    glow.style.setProperty('--mouse-y', e.clientY + 'px');
  });

  function pill(eventType) {
    const t = String(eventType || '').toLowerCase();
    if (t === 'auth') return 'type-auth';
    if (t === 'transaction') return 'type-txn';
    if (t === 'admin') return 'type-admin';
    return 'type-sec';
  }

  function fmtDate(ts) {
    const d = new Date(ts);
    if (Number.isNaN(d.getTime())) return { time: '—', date: '—' };
    return {
      time: d.toLocaleTimeString(),
      date: d.toLocaleDateString()
    };
  }

  async function loadLogs() {
    const tbody = document.getElementById('logsTbody');
    if (!tbody) return;

    const params = new URLSearchParams();
    const search = document.getElementById('logSearch')?.value?.trim();
    const eventType = document.getElementById('eventType')?.value;
    const start = document.getElementById('startDate')?.value;
    const end = document.getElementById('endDate')?.value;

    if (search) params.set('search', search);
    if (eventType) params.set('event_type', eventType);
    if (start) params.set('start_date', start);
    if (end) params.set('end_date', end);

    try {
      const res = await window.api.authenticatedFetch('/api/admin/system_logs.php' + (params.toString() ? `?${params}` : ''));
      const logs = Array.isArray(res?.logs) ? res.logs : [];

      tbody.innerHTML = logs.map(l => {
        const dt = fmtDate(l.created_at);
        const statusOk = String(l.status || '').toLowerCase() === 'success';
        const statusColor = statusOk ? 'var(--success)' : 'var(--danger)';
        const statusIcon = statusOk ? 'ph-check-circle' : 'ph-x-circle';
        return `
          <tr>
            <td>
              <span class="log-timestamp">${dt.time}</span>
              <span class="log-date">${dt.date}</span>
            </td>
            <td><span class="type-pill ${pill(l.event_type)}">${l.event_type}</span></td>
            <td>
              <span class="log-desc">${l.description || '—'}</span>
              <span class="log-actor">${l.actor ? `By: ${l.actor}` : ''}</span>
            </td>
            <td style="font-family: monospace;">${l.ip || '—'}</td>
            <td style="color: ${statusColor};"><i class="ph ${statusIcon}"></i> ${l.status || '—'}</td>
          </tr>
        `;
      }).join('') || `
        <tr>
          <td colspan="5" style="padding: 18px 20px; color: var(--text-3);">No logs found.</td>
        </tr>
      `;
    } catch (e) {
      console.error('Logs load failed:', e);
      tbody.innerHTML = `
        <tr>
          <td colspan="5" style="padding: 18px 20px; color: var(--danger);">Failed to load logs.</td>
        </tr>
      `;
    }
  }

  function downloadCsv() {
    const tbody = document.getElementById('logsTbody');
    if (!tbody) return;
    const rows = [['Timestamp', 'Event Type', 'Description', 'Actor', 'IP', 'Status']];
    tbody.querySelectorAll('tr').forEach(tr => {
      const cols = Array.from(tr.querySelectorAll('td')).map(td => td.innerText.replace(/\s+/g, ' ').trim());
      if (cols.length) rows.push(cols);
    });
    const csv = rows.map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'system_logs.csv';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  }

  document.addEventListener('DOMContentLoaded', loadLogs);
</script>

<script>
        // Global Pending Badge Updater
        async function updateGlobalPendingBadge() {
            try {
                let totalPending = 0;
                
                // 1. Get Pending Transaction Requests
                const statsRes = await window.api.authenticatedFetch('/api/admin/dashboard_stats.php');
                if (statsRes && statsRes.stats) {
                    totalPending += Number(statsRes.stats.pending_approvals || 0);
                }
                
                // 2. Get Pending Asset Approvals
                const assetsRes = await window.api.authenticatedFetch('/api/assets/admin_approval.php');
                if (assetsRes && assetsRes.pending_assets) {
                    totalPending += assetsRes.pending_assets.length;
                }

                // 3. Update the Badge
                const badge = document.getElementById('pendingNavBadge');
                const pendingLink = document.getElementById('pendingNavLink');
                if (badge) {
                    badge.textContent = String(totalPending);
                    badge.style.display = totalPending > 0 ? 'inline-flex' : 'none';
                }
                if (pendingLink) {
                    if (totalPending > 0) {
                        pendingLink.classList.add('has-notif');
                    } else {
                        pendingLink.classList.remove('has-notif');
                    }
                }
            } catch (error) {
                console.error('Failed to update pending badge:', error);
            }
        }

        // Run immediately on load, then every 15 seconds
        document.addEventListener('DOMContentLoaded', () => {
            updateGlobalPendingBadge();
            setInterval(updateGlobalPendingBadge, 15000);
        });
    </script>

  <script src="../js/theme.js?v=1778041298"></script>
</body>
</html>