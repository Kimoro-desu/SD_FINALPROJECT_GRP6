<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HariBorrow — Admin Console</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600&family=Pinyon+Script&display=swap"
    rel="stylesheet">
  <script src="../js/api.js"></script>
  <script src="../js/auth_guard.js"></script>
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      --bg-deep: #030304;
      --glass: rgba(15, 15, 20, 0.45);
      --glass-heavy: rgba(10, 10, 13, 0.85);
      --glass-border: rgba(255, 255, 255, 0.08);
      --gold: #E5C07B;
      --gold-light: #FCEBAF;
      --gold-dark: #A68A48;
      --gold-dim: rgba(229, 192, 123, 0.1);
      --gold-glow: rgba(229, 192, 123, 0.25);
      --text-1: #FFFFFF;
      --text-2: #A39E93;
      --text-3: #6B665A;
      --sidebar-w: 260px;
      --danger: #ff6b7a;
      --success: #4ade80;
    }

    html,
    body {
      height: 100vh;
      width: 100vw;
      overflow: hidden;
      font-family: 'Outfit', sans-serif;
      background-color: var(--bg-deep);
      color: var(--text-1);
      display: flex;
    }

    ::-webkit-scrollbar {
      width: 6px;
      background: transparent;
    }

    ::-webkit-scrollbar-thumb {
      background: var(--text-3);
      border-radius: 10px;
    }

    /* ── AESTHETIC BACKGROUND ── */
    .bg-mesh {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      z-index: 0;
      background-size: cover;
      background-position: center;
      opacity: 0.15;
      background-color: var(--bg-deep);
      background-blend-mode: overlay;
      background:
        radial-gradient(circle at 10% 90%, rgba(229, 192, 123, 0.05), transparent 50%),
        radial-gradient(circle at 90% 10%, rgba(166, 138, 72, 0.05), transparent 50%),
        var(--bg-deep);
    }

    .ambient-glow {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      pointer-events: none;
      background: radial-gradient(480px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(229, 192, 123, 0.1), rgba(229, 192, 123, 0.03) 38%, transparent 68%);
      z-index: 9999;
      mix-blend-mode: screen;
      transition: background 0.08s ease-out;
    }

    /* ── SIDEBAR NAVIGATION ── */
    .sidebar {
      width: var(--sidebar-w);
      height: 100vh;
      background: var(--glass-heavy);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border-right: 1px solid var(--glass-border);
      display: flex;
      flex-direction: column;
      z-index: 100;
      position: relative;
    }

    .sidebar-header {
      padding: 32px 24px;
      border-bottom: 1px solid var(--glass-border);
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .nav-logo {
      height: 48px;
      width: auto;
      object-fit: contain;
      filter: drop-shadow(0 0 14px rgba(229, 192, 123, 0.4)) brightness(1.25) contrast(1.35) saturate(1.55);
    }

    .nav-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 22px;
      font-weight: 600;
      background: linear-gradient(135deg, #FFF 0%, var(--gold-light) 50%, var(--gold-dark) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      letter-spacing: 0.02em;
      line-height: 1;
    }

    .admin-badge {
      font-family: 'Outfit', sans-serif;
      font-size: 8px;
      font-weight: 600;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: var(--gold);
      border: 1px solid var(--gold-dim);
      padding: 2px 6px;
      border-radius: 4px;
      margin-top: 4px;
      display: inline-block;
    }

    .nav-menu {
      padding: 24px 12px;
      display: flex;
      flex-direction: column;
      gap: 6px;
      flex-grow: 1;
      overflow-y: auto;
    }

    .nav-section-title {
      font-size: 10px;
      font-weight: 600;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: var(--text-3);
      margin: 16px 0 8px 12px;
    }

    .nav-link {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      color: var(--text-2);
      text-decoration: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 400;
      transition: all 0.3s;
      border: 1px solid transparent;
      position: relative;
    }

    .nav-link i {
      font-size: 20px;
      color: var(--text-3);
      transition: color 0.2s;
    }

    .nav-link:hover {
      background: rgba(255, 255, 255, 0.03);
      color: var(--text-1);
    }

    .nav-link.active {
      background: var(--gold-dim);
      border-color: rgba(229, 192, 123, 0.2);
      color: var(--gold-light);
    }

    .nav-link.active i {
      color: var(--gold);
    }

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
    /* Active + has-notif: keep the gold active style but add a subtle danger accent */
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

    .sidebar-footer {
      padding: 24px;
      border-top: 1px solid var(--glass-border);
    }

    .admin-profile {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .admin-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: var(--gold-dark);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      font-size: 14px;
      color: var(--bg-deep);
    }

    .admin-info {
      display: flex;
      flex-direction: column;
    }

    .admin-name {
      font-size: 14px;
      font-weight: 500;
      color: var(--text-1);
    }

    .admin-role {
      font-size: 11px;
      color: var(--text-3);
    }

    /* ── MAIN CONTENT AREA ── */
    .main-content {
      flex-grow: 1;
      height: 100vh;
      overflow-y: auto;
      position: relative;
      z-index: 10;
      padding: 40px 48px;
    }

    .header-area {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      margin-bottom: 40px;
    }

    .page-title h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 42px;
      font-weight: 400;
      color: var(--text-1);
      line-height: 1.1;
      margin-bottom: 8px;
    }

    .page-title p {
      font-size: 14px;
      color: var(--text-2);
      font-weight: 300;
      letter-spacing: 0.03em;
    }

    /* Quick Stats Row */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 24px;
      margin-bottom: 40px;
    }

    .stat-card {
      background: var(--glass);
      border: 1px solid var(--glass-border);
      border-radius: 12px;
      padding: 24px;
      position: relative;
      overflow: hidden;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: var(--gold);
      opacity: 0.5;
    }

    .stat-title {
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--text-2);
      margin-bottom: 12px;
    }

    .stat-value {
      font-family: 'Cormorant Garamond', serif;
      font-size: 36px;
      color: var(--text-1);
      font-weight: 500;
      line-height: 1;
    }

    .stat-sub {
      font-size: 12px;
      color: var(--text-3);
      margin-top: 8px;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .stat-sub.positive {
      color: var(--success);
    }

    .stat-sub.negative {
      color: var(--danger);
    }

    /* Data Table Area */
    .data-panel {
      background: var(--glass);
      border: 1px solid var(--glass-border);
      border-radius: 12px;
      overflow: hidden;
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
    }

    .panel-header {
      padding: 24px;
      border-bottom: 1px solid var(--glass-border);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .panel-title {
      font-size: 16px;
      font-weight: 500;
      color: var(--text-1);
      letter-spacing: 0.02em;
    }

    .panel-actions {
      display: flex;
      gap: 12px;
    }

    .btn-outline {
      background: transparent;
      border: 1px solid var(--glass-border);
      color: var(--text-2);
      padding: 8px 16px;
      border-radius: 6px;
      font-family: 'Outfit', sans-serif;
      font-size: 12px;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-outline:hover {
      border-color: var(--gold);
      color: var(--gold);
      background: var(--gold-dim);
    }

    /* Mock Table */
    table {
      width: 100%;
      border-collapse: collapse;
    }

    th {
      text-align: left;
      padding: 16px 24px;
      font-size: 10px;
      font-weight: 600;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--text-3);
      border-bottom: 1px solid var(--glass-border);
      background: rgba(0, 0, 0, 0.2);
    }

    td {
      padding: 16px 24px;
      font-size: 14px;
      color: var(--text-2);
      font-weight: 300;
      border-bottom: 1px solid rgba(255, 255, 255, 0.03);
    }

    tr:hover td {
      background: rgba(255, 255, 255, 0.02);
    }

    .status-pill {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 10px;
      font-weight: 600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      display: inline-block;
    }

    .status-pill.pending {
      background: rgba(229, 192, 123, 0.1);
      color: var(--gold);
      border: 1px solid rgba(229, 192, 123, 0.3);
    }

    .status-pill.active {
      background: rgba(74, 222, 128, 0.1);
      color: var(--success);
      border: 1px solid rgba(74, 222, 128, 0.3);
    }

    .status-pill.overdue {
      background: rgba(255, 107, 122, 0.1);
      color: var(--danger);
      border: 1px solid rgba(255, 107, 122, 0.3);
    }

    .status-pill.returned {
      background: rgba(96, 165, 250, 0.1);
      color: #60a5fa;
      border: 1px solid rgba(96, 165, 250, 0.3);
    }

    @media (max-width: 980px) {
      html,
      body {
        height: auto;
        min-height: 100vh;
        width: 100%;
        overflow: auto;
        display: block;
      }

      .sidebar {
        width: 100%;
        height: auto;
        border-right: none;
        border-bottom: 1px solid var(--glass-border);
      }

      .sidebar-header,
      .sidebar-footer {
        padding: 16px;
      }

      .nav-menu {
        padding: 12px;
        overflow: visible;
      }

      .nav-link {
        padding: 10px 12px;
        font-size: 13px;
      }

      .main-content {
        height: auto;
        min-height: calc(100vh - 180px);
        padding: 20px 16px;
      }

      .header-area {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
      }

      .stats-grid {
        grid-template-columns: 1fr;
        gap: 12px;
      }

      .panel-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }

      table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
      }
    }
  </style>
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
      <a href="admin_dashboard.php" class="nav-link active">
        <i class="ph ph-squares-four"></i> Command Center
      </a>

      <div class="nav-section-title">Operations</div>
      <a href="pendingrequest_approval.php" class="nav-link" id="pendingNavLink">
        <i class="ph ph-bell-ringing"></i> Pending Requests
        <span class="nav-badge" id="pendingNavBadge" style="display:none;">0</span>
      </a>
      <a href="active_transactions.php" class="nav-link">
        <i class="ph ph-clock-counter-clockwise"></i> All Transactions
      </a>

      <div class="nav-section-title">Database</div>
      <a href="asset_inventory.php" class="nav-link">
        <i class="ph ph-stack"></i> Asset Inventory
      </a>
      <a href="registered_users.php" class="nav-link">
        <i class="ph ph-users"></i> Registered Users
      </a>

      <div class="nav-section-title">System</div>
      <a href="registration_approval.php" class="nav-link">
        <i class="ph ph-shield-check"></i> Registration Approvals
      </a>
      <a href="system_logs.php" class="nav-link">
        <i class="ph ph-file-text"></i> System Logs
      </a>
    </nav>

    <div class="sidebar-footer">
      <div class="admin-profile">
        <div class="admin-avatar" id="adminAvatar">SA</div>
        <div class="admin-info">
          <span class="admin-name" id="adminName">System Admin</span>
          <span class="admin-role" id="adminRole">Admin</span>
        </div>
        <a href="javascript:void(0)" onclick="window.api.removeToken(); window.location.href='login.php'"
          style="margin-left: auto; color: var(--text-3); cursor: pointer;"><i class="ph ph-sign-out"
            style="font-size: 20px;"></i></a>
      </div>
    </div>
  </aside>

  <main class="main-content">

    <div class="header-area">
      <div class="page-title">
        <h1>Command Center</h1>
        <p>System metrics and recent facility transactions.</p>
      </div>

      <button class="btn-outline" id="exportDailyReportBtn" style="border-color: var(--gold); color: var(--gold);"><i
          class="ph ph-download-simple"></i> Export Daily Report</button>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-title">Pending Approvals</div>
        <div class="stat-value" id="statPendingApprovals">—</div>
        <div class="stat-sub"><i class="ph ph-clock"></i> Requires immediate action</div>
      </div>
      <div class="stat-card">
        <div class="stat-title">Active Borrowings</div>
        <div class="stat-value" id="statActiveTransactions">—</div>
        <div class="stat-sub positive"><i class="ph ph-trend-up"></i> Currently dispatched</div>
      </div>
      <div class="stat-card">
        <div class="stat-title">Overdue Assets</div>
        <div class="stat-value" id="statOverdueAssets" style="color: var(--danger);">—</div>
        <div class="stat-sub negative"><i class="ph ph-warning"></i> Require administrative action</div>
      </div>
      <div class="stat-card">
        <div class="stat-title">Total Registered Users</div>
        <div class="stat-value" id="statTotalUsers">—</div>
        <div class="stat-sub"><i class="ph ph-users"></i> Active platform users</div>
      </div>
    </div>

    <div class="data-panel">
      <div class="panel-header">
        <div class="panel-title">Recent Transactions Logging</div>
        <div class="panel-actions">
          <button class="btn-outline"><i class="ph ph-funnel"></i> Filter</button>
          <button class="btn-outline"><i class="ph ph-magnifying-glass"></i> Search</button>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Transaction ID</th>
            <th>Borrower</th>
            <th>Asset Requested</th>
            <th>Request Date</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="recentTransactionsBody"></tbody>
      </table>
    </div>

    <div class="data-panel" style="margin-top: 22px;">
      <div class="panel-header">
        <div class="panel-title">Pending Asset Approvals</div>
      </div>
      <table>
        <thead>
          <tr>
            <th>Asset</th>
            <th>Type</th>
            <th>Meetup Location</th>
            <th>Lender</th>
            <th>Submitted</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="pendingAssetsBody"></tbody>
      </table>
    </div>

  </main>

  <script>
    // Mouse Glow Effect
    const glow = document.getElementById('glow');
    document.addEventListener('mousemove', (e) => {
      glow.style.setProperty('--mouse-x', e.clientX + 'px');
      glow.style.setProperty('--mouse-y', e.clientY + 'px');
    });

    // Sidebar active link state (no click interception; allow navigation)
    document.addEventListener('DOMContentLoaded', () => {
      const path = window.location.pathname.toLowerCase();
      document.querySelectorAll('.nav-link').forEach(a => {
        const href = (a.getAttribute('href') || '').toLowerCase();
        if (href && path.endsWith(href)) a.classList.add('active');
        else a.classList.remove('active');
      });
    });

    // Dynamic Admin Profile Loading
    document.addEventListener('DOMContentLoaded', () => {
      // Small delay to ensure auth_guard finishes fetching if needed
      setTimeout(() => {
        let user = window.api ? window.api.getUser() : null;
        if (user && user.name) {
          const firstName = user.name.split(' ')[0] || 'Admin';
          const lastName = user.name.split(' ').slice(1).join(' ') || '';
          const initial = firstName.charAt(0).toUpperCase();

          const avatar = document.getElementById('adminAvatar');
          if (avatar) avatar.textContent = initial;

          const nameSpan = document.getElementById('adminName');
          if (nameSpan) nameSpan.textContent = `${firstName} ${lastName}`.trim();

          const roleSpan = document.getElementById('adminRole');
          if (roleSpan) {
            const rawRole = (user.role || '').trim().toLowerCase();
            roleSpan.textContent = rawRole ? (rawRole.charAt(0).toUpperCase() + rawRole.slice(1)) : 'Admin';
          }
        }
      }, 300);
    });

    function fmtDate(val) {
      if (!val) return '—';
      const d = new Date(val);
      if (Number.isNaN(d.getTime())) return String(val);
      return d.toLocaleString();
    }

    function pillClass(status) {
      const s = String(status || '').toLowerCase();
      if (s.includes('pending')) return 'pending';
      if (s.includes('returned') || s.includes('completed')) return 'returned';
      if (s.includes('approved') || s.includes('confirmed') || s.includes('active')) return 'active';
      if (s.includes('overdue')) return 'overdue';
      if (s.includes('rejected')) return 'overdue';
      return 'pending';
    }

    function toCsvValue(value) {
      if (value === null || value === undefined) return '';
      const text = String(value).replace(/"/g, '""');
      return /[",\r\n]/.test(text) ? `"${text}"` : text;
    }

    function dateOnlyValue(value) {
      if (!value) return '';
      const d = new Date(value);
      if (Number.isNaN(d.getTime())) return '';
      const y = d.getFullYear();
      const m = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      return `${y}-${m}-${day}`;
    }

    function downloadCsv(filename, rows) {
      const csv = rows.map(cols => cols.map(toCsvValue).join(',')).join('\r\n');
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);
    }

    async function exportDailyReport() {
      const btn = document.getElementById('exportDailyReportBtn');
      const originalHtml = btn ? btn.innerHTML : '';
      if (btn) {
        btn.disabled = true;
        btn.style.opacity = '0.7';
        btn.innerHTML = '<i class="ph ph-spinner-gap"></i> Exporting...';
      }

      try {
        const [statsRes, histRes, pendingRes] = await Promise.all([
          window.api.authenticatedFetch('/admin/dashboard_stats.php'),
          window.api.authenticatedFetch('/transactions/history.php'),
          window.api.authenticatedFetch('/assets/admin_approval.php')
        ]);

        const stats = statsRes?.stats || {};
        const history = Array.isArray(histRes?.history) ? histRes.history : [];
        const pendingAssets = Array.isArray(pendingRes?.pending_assets) ? pendingRes.pending_assets : [];

        const today = dateOnlyValue(new Date());
        const dailyTransactions = history.filter(tx => {
          const source = tx?.dates?.borrowed || tx?.dates?.requested;
          return dateOnlyValue(source) === today;
        });
        const dailyPendingAssets = pendingAssets.filter(a => dateOnlyValue(a?.created_at) === today);

        const now = new Date();
        const generatedAt = now.toISOString();
        const rows = [
          ['HariBorrow Admin Daily Report'],
          ['Generated At', generatedAt],
          ['Report Date', today],
          [],
          ['Dashboard Summary'],
          ['Metric', 'Value'],
          ['Pending Approvals', stats.pending_approvals ?? 0],
          ['Active Borrowings', stats.active_transactions ?? 0],
          ['Overdue Assets', stats.overdue_assets ?? 0],
          ['Total Registered Users', stats.total_users ?? 0],
          [],
          ['Transactions Created Today'],
          ['Transaction ID', 'Borrower', 'Borrower School ID', 'Asset', 'Status', 'Requested Date', 'Borrowed Date', 'Due Date', 'Returned Date', 'Overdue'],
          ...dailyTransactions.map(tx => [
            tx?.transaction_id ?? '',
            tx?.borrower?.name || '',
            tx?.borrower?.school_id || '',
            tx?.asset?.name || '',
            tx?.status || '',
            tx?.dates?.requested || '',
            tx?.dates?.borrowed || '',
            tx?.dates?.due || '',
            tx?.dates?.returned || '',
            tx?.is_overdue ? 'Yes' : 'No'
          ]),
          []
        ];

        if (dailyTransactions.length === 0) {
          rows.push(['No transactions found for today.']);
          rows.push([]);
        }

        rows.push(['Pending Asset Submissions Today']);
        rows.push(['Asset', 'Type', 'Meetup Location', 'Lender', 'Status', 'Submitted']);
        rows.push(...dailyPendingAssets.map(a => [
          a?.name || '',
          a?.type || '',
          a?.meetup_location || '',
          a?.lender_name || '',
          a?.status || '',
          a?.created_at || ''
        ]));

        if (dailyPendingAssets.length === 0) {
          rows.push(['No pending asset submissions found for today.']);
        }

        downloadCsv(`daily_report_${today}.csv`, rows);
      } catch (e) {
        console.error('Daily report export failed:', e);
        alert(e?.message || 'Failed to export daily report.');
      } finally {
        if (btn) {
          btn.disabled = false;
          btn.style.opacity = '1';
          btn.innerHTML = originalHtml;
        }
      }
    }

    async function loadAdminDashboard() {
      // Stats
      try {
        const statsRes = await window.api.authenticatedFetch('/admin/dashboard_stats.php');
        const s = statsRes?.stats || {};
        const elPending = document.getElementById('statPendingApprovals');
        const elActive = document.getElementById('statActiveTransactions');
        const elOverdue = document.getElementById('statOverdueAssets');
        const elUsers = document.getElementById('statTotalUsers');
        if (elPending) elPending.textContent = (s.pending_approvals ?? 0);
        if (elActive) elActive.textContent = (s.active_transactions ?? 0);
        if (elOverdue) elOverdue.textContent = (s.overdue_assets ?? 0);
        if (elUsers) elUsers.textContent = (s.total_users ?? 0);
      } catch (e) {
        console.error('Admin stats load failed:', e);
      }

      // Recent transactions
      try {
        const histRes = await window.api.authenticatedFetch('/transactions/history.php');
        const history = Array.isArray(histRes?.history) ? histRes.history : [];
        const rows = history.slice(0, 8).map(tx => {
          const txId = tx?.transaction_id ? `#${tx.transaction_id}` : '—';
          const borrowerName = tx?.borrower?.name || '—';
          const borrowerDept = tx?.borrower?.school_id || '';
          const asset = tx?.asset?.name || '—';
          const requested = fmtDate(tx?.dates?.borrowed || tx?.dates?.requested);
          
          let statusRaw = tx?.status || '—';
          let status = statusRaw;
          
          // Determine exact status state
          if (tx?.dates?.returned) {
            status = 'Returned';
          } else if (tx?.is_overdue) {
            status = 'Overdue';
          }
          
          const cls = pillClass(status);
          
          return `
            <tr>
              <td style="color: var(--text-1); font-family: monospace;">${txId}</td>
              <td>${borrowerName}${borrowerDept ? ` <br><span style="font-size: 11px; color: var(--text-3);">${borrowerDept}</span>` : ''}</td>
              <td style="color: var(--text-1);">${asset}</td>
              <td>${requested}</td>
              <td><span class="status-pill ${cls}">${status}</span></td>
              <td><a class="btn-outline" href="active_transactions.php" style="padding: 4px 12px; text-decoration: none;">View</a></td>
            </tr>
          `;
        }).join('');

        const body = document.getElementById('recentTransactionsBody');
        if (body) body.innerHTML = rows || `
          <tr>
            <td colspan="6" style="padding: 18px 24px; color: var(--text-3);">No transactions found.</td>
          </tr>
        `;
      } catch (e) {
        console.error('Transaction history load failed:', e);
      }

      // Pending asset approvals
      try {
        const pendingRes = await window.api.authenticatedFetch('/assets/admin_approval.php');
        const pending = Array.isArray(pendingRes?.pending_assets) ? pendingRes.pending_assets : [];
        const body = document.getElementById('pendingAssetsBody');
        if (!body) return;
        body.innerHTML = pending.map(a => `
          <tr>
            <td style="color: var(--text-1);">${a.name || '—'}</td>
            <td>${a.type || '—'}</td>
            <td>${a.meetup_location || '—'}</td>
            <td>${a.lender_name || '—'}</td>
            <td>${fmtDate(a.created_at)}</td>
            <td><span class="status-pill pending">${a.status || 'pending'}</span></td>
            <td><button class="btn-outline" style="padding: 4px 12px;" onclick="approvePendingAsset(${Number(a.id)})">Approve</button></td>
          </tr>
        `).join('') || `<tr><td colspan="7" style="padding: 18px 24px; color: var(--text-3);">No pending assets.</td></tr>`;

        // Sidebar pending badge = pending transactions + pending assets
        const pendingTx = Number(document.getElementById('statPendingApprovals')?.textContent || 0);
        const pendingTotal = pendingTx + pending.length;
        const badge = document.getElementById('pendingNavBadge');
        const pendingLink = document.getElementById('pendingNavLink');
        if (badge) {
          badge.textContent = String(pendingTotal);
          badge.style.display = pendingTotal > 0 ? 'inline-flex' : 'none';
        }
        // Highlight the sidebar link when there are pending items
        if (pendingLink) {
          if (pendingTotal > 0) {
            pendingLink.classList.add('has-notif');
          } else {
            pendingLink.classList.remove('has-notif');
          }
        }
      } catch (e) {
        console.error('Pending assets load failed:', e);
      }
    }

    async function approvePendingAsset(assetId) {
      try {
        await window.api.authenticatedFetch('/assets/admin_approval.php', {
          method: 'POST',
          body: { id: assetId, status: 'approved' }
        });
        await loadAdminDashboard();
      } catch (e) {
        alert(e?.message || 'Failed to approve asset.');
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      const exportBtn = document.getElementById('exportDailyReportBtn');
      if (exportBtn) {
        exportBtn.addEventListener('click', exportDailyReport);
      }
      loadAdminDashboard();
      // Keep sidebar/dashboard counts fresh when new uploads arrive.
      setInterval(loadAdminDashboard, 15000);
    });
  </script>

</body>

</html>