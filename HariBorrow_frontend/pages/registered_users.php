<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HariBorrow — Registered Users</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600&display=swap"
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
      --success: #4ade80;
      --danger: #ff6b7a;
      --warning: #fbbf24;
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
      flex-shrink: 0;
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
      transition: all 0.2s;
      border: 1px solid transparent;
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

    .nav-badge {
            margin-left: auto;
            background: var(--danger);
            color: #fff;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
            min-width: 18px;
            text-align: center;
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

    /* ── MAIN CONTENT ── */
    .main-content {
      flex-grow: 1;
      height: 100vh;
      overflow-y: auto;
      position: relative;
      z-index: 10;
      padding: 40px 48px;
      display: flex;
      flex-direction: column;
    }

    .header-area {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      margin-bottom: 32px;
      gap: 20px;
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

    .btn-outline {
      background: transparent;
      border: 1px solid var(--glass-border);
      color: var(--text-2);
      padding: 11px 16px;
      border-radius: 10px;
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      cursor: pointer;
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      height: fit-content;
    }

    .btn-outline:hover {
      border-color: var(--gold);
      color: var(--gold);
      background: var(--gold-dim);
    }

    /* ── TOOLBAR ── */
    .toolbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
      flex-wrap: wrap;
      gap: 16px;
    }

    .filters {
      display: flex;
      gap: 12px;
      flex: 1;
      flex-wrap: wrap;
    }

    .search-wrap {
      position: relative;
      flex: 1;
      max-width: 360px;
      min-width: 220px;
    }

    .search-wrap i {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-3);
      font-size: 18px;
      pointer-events: none;
    }

    .search-input {
      width: 100%;
      background: var(--glass);
      border: 1px solid var(--glass-border);
      border-radius: 10px;
      padding: 11px 16px 11px 42px;
      color: var(--text-1);
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      outline: none;
      transition: border-color 0.2s;
    }

    .search-input:focus {
      border-color: rgba(229, 192, 123, 0.4);
    }

    .filter-select {
      background: var(--glass);
      border: 1px solid var(--glass-border);
      border-radius: 10px;
      padding: 11px 36px 11px 14px;
      color: var(--text-2);
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      outline: none;
      cursor: pointer;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236B665A' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
    }

    .filter-select:focus {
      border-color: var(--gold);
      color: var(--text-1);
    }

    /* ── DATA TABLE ── */
    .data-panel {
      background: var(--glass);
      border: 1px solid var(--glass-border);
      border-radius: 12px;
      overflow: hidden;
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th {
      text-align: left;
      padding: 16px 20px;
      font-size: 10px;
      font-weight: 600;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--text-3);
      border-bottom: 1px solid var(--glass-border);
      background: rgba(0, 0, 0, 0.2);
    }

    td {
      padding: 16px 20px;
      font-size: 14px;
      color: var(--text-2);
      font-weight: 300;
      border-bottom: 1px solid rgba(255, 255, 255, 0.03);
      vertical-align: middle;
    }

    tr:hover td {
      background: rgba(255, 255, 255, 0.02);
    }

    .user-cell {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .user-avatar-sm {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--gold-dim);
      border: 1px solid rgba(229, 192, 123, 0.3);
      color: var(--gold);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      font-size: 14px;
      flex-shrink: 0;
    }

    .user-name-txt {
      color: var(--text-1);
      font-weight: 500;
      display: block;
      margin-bottom: 2px;
    }

    .user-email-txt {
      font-size: 11px;
      color: var(--text-3);
    }

    .status-pill {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 10px;
      font-weight: 600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .status-pill::before {
      content: '';
      display: block;
      width: 6px;
      height: 6px;
      border-radius: 50%;
    }

    .status-active {
      background: rgba(74, 222, 128, 0.1);
      color: var(--success);
      border: 1px solid rgba(74, 222, 128, 0.2);
    }

    .status-active::before {
      background: var(--success);
    }

    .status-restricted {
      background: rgba(255, 107, 122, 0.1);
      color: var(--danger);
      border: 1px solid rgba(255, 107, 122, 0.2);
    }

    .status-restricted::before {
      background: var(--danger);
    }

    .status-suspended {
      background: rgba(251, 191, 36, 0.12);
      color: var(--warning);
      border: 1px solid rgba(251, 191, 36, 0.25);
    }

    .status-suspended::before {
      background: var(--warning);
    }

    .btn-action {
      background: transparent;
      border: 1px solid var(--glass-border);
      color: var(--gold);
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 12px;
      cursor: pointer;
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .btn-action:hover {
      background: var(--gold-dim);
      border-color: var(--gold);
    }

    /* ── MODAL ── */
    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(8px);
      z-index: 200;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s;
    }

    .modal-overlay.active {
      opacity: 1;
      pointer-events: auto;
    }

    .modal {
      background: rgba(15, 15, 20, 0.95);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      padding: 32px 40px;
      width: 100%;
      max-width: 500px;
      transform: scale(0.95);
      transition: transform 0.3s;
    }

    .modal-overlay.active .modal {
      transform: scale(1);
    }

    .modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
      border-bottom: 1px solid var(--glass-border);
      padding-bottom: 16px;
    }

    .modal-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 26px;
      font-weight: 500;
      color: var(--text-1);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .modal-close {
      background: transparent;
      border: 1px solid var(--glass-border);
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--text-3);
      font-size: 18px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .modal-close:hover {
      border-color: var(--danger);
      color: var(--danger);
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--text-3);
      display: block;
      margin-bottom: 8px;
    }

    .form-select,
    .form-textarea,
    .form-input {
      width: 100%;
      background: rgba(255, 255, 255, 0.02);
      border: 1px solid var(--glass-border);
      border-radius: 10px;
      padding: 14px 16px;
      color: var(--text-1);
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      outline: none;
      transition: border-color 0.2s;
    }

    .form-select:focus,
    .form-textarea:focus,
    .form-input:focus {
      border-color: var(--gold);
    }

    .form-textarea {
      resize: vertical;
      min-height: 80px;
    }

    .form-input[readonly] {
      background: transparent;
      border-color: transparent;
      padding-left: 0;
      font-size: 18px;
      color: var(--gold);
    }

    .btn-submit {
      width: 100%;
      background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
      border: none;
      padding: 16px;
      border-radius: 10px;
      color: var(--bg-deep);
      font-family: 'Outfit', sans-serif;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      margin-top: 12px;
    }

    .btn-submit:hover {
      box-shadow: 0 4px 15px var(--gold-glow);
      transform: translateY(-2px);
    }

    .filter-select option,
    .form-select option {
      background-color: #1a1a22;
      color: #e2ddd6;
      padding: 8px 12px;
      font-family: 'Outfit', sans-serif;
    }

    .filter-select option:checked,
    .form-select option:checked {
      background-color: #2a2a35;
      color: var(--gold-light);
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
      <a href="admin_dashboard.php" class="nav-link">
        <i class="ph ph-squares-four"></i> Command Center
      </a>

      <div class="nav-section-title">Operations</div>
      <a href="pendingrequest_approval.php" class="nav-link"><i class="ph ph-bell-ringing"></i> Pending Requests
        <span class="nav-badge" id="pendingNavBadge" style="display:none;">0</span>
      </a>
      <a href="active_transactions.php" class="nav-link"><i class="ph ph-clock-counter-clockwise"></i> All Transactions</a>

      <div class="nav-section-title">Database</div>
      <a href="asset_inventory.php" class="nav-link">
        <i class="ph ph-stack"></i> Asset Inventory
      </a>
      <a href="registered_users.php" class="nav-link active">
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
          style="margin-left: auto; color: var(--text-3); cursor: pointer;">
          <i class="ph ph-sign-out" style="font-size: 20px;"></i>
        </a>
      </div>
    </div>
  </aside>

  <main class="main-content">
    <div class="header-area">
      <div class="page-title">
        <h1>Registered Users</h1>
        <p>Manage student and faculty accounts, operational roles, and administrative holds.</p>
      </div>
      <button type="button" class="btn-outline" id="exportUsersBtn"><i class="ph ph-download-simple"></i> Export User List</button>
    </div>

    <div class="toolbar">
      <div class="filters">
        <div class="search-wrap">
          <i class="ph ph-magnifying-glass"></i>
          <input class="search-input" type="text" id="userSearchInput" name="search" placeholder="Search by Name, Student ID, or Email...">
        </div>

        <select class="filter-select" id="deptFilter" name="dept_filter">
          <option value="">All Departments</option>
          <option value="coe">College of Engineering (COE)</option>
          <option value="cos">College of Science (COS)</option>
          <option value="cba">College of Business Admin (CBA)</option>
        </select>

        <select class="filter-select" id="statusFilter" name="status_filter">
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="restricted">Restricted (Hold)</option>
          <option value="suspended">Suspended</option>
        </select>
      </div>
    </div>

    <div class="data-panel">
      <table>
        <thead>
          <tr>
            <th>User Details</th>
            <th>University ID</th>
            <th>Department / College</th>
            <th>Account Role</th>
            <th>Status</th>
            <th style="text-align: right;">Action</th>
          </tr>
        </thead>
        <tbody id="usersTbody"></tbody>
      </table>
    </div>
  </main>

  <div class="modal-overlay" id="userModal">
    <div class="modal">
      <div class="modal-header">
        <div class="modal-title">
          <i class="ph ph-user-gear" style="color: var(--gold);"></i>
          <span>Manage Account</span>
        </div>
        <button class="modal-close" onclick="closeUserModal()"><i class="ph ph-x"></i></button>
      </div>

      <form id="userStatusForm">
        <input type="hidden" name="user_id" id="hiddenUserId" value="">

        <div class="form-group">
          <label class="form-label">User Account</label>
          <input type="text" class="form-input" id="displayUserName" readonly>
        </div>

        <div class="form-group">
          <label class="form-label">Account Status Override</label>
          <select class="form-select" name="account_status" id="formStatusSelect" required>
            <option value="active">Active (Clear for Borrowing)</option>
            <option value="restricted">Restricted (Administrative Hold)</option>
            <option value="suspended">Suspended (Ban)</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">ID Verification Status</label>
          <div id="idPhotoContainer" style="margin-bottom: 12px; display: none;">
             <a id="idPhotoLink" href="#" target="_blank" style="color: var(--gold); font-size: 13px; text-decoration: underline;"><i class="ph ph-image"></i> View Uploaded ID Photo</a>
          </div>
          <select class="form-select" name="id_verification_status" id="formIdVerifySelect" required>
            <option value="unverified">Unverified</option>
            <option value="pending">Pending Approval</option>
            <option value="verified">Verified</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>

        <div class="form-group" id="remarksGroup">
          <label class="form-label">Reason for Restriction / Admin Notes</label>
          <textarea class="form-textarea" name="admin_notes" id="modalAdminNotes"
            placeholder="Required if applying a restriction or suspension."></textarea>
        </div>

        <button type="submit" class="btn-submit">
          <i class="ph ph-floppy-disk"></i> Update User Status
        </button>
      </form>
    </div>
  </div>

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

    // Dynamic Admin Profile Loading (same logic as admin_dashboard.php)
    document.addEventListener('DOMContentLoaded', () => {
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

    /** In-memory copy of users for filtering / export (set by loadUsers). */
    let allUsers = [];

    function escapeHtml(s) {
      return String(s ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    // Modal logic
    function openUserModal(userId, userName, currentStatus, accountNotes, idVerifyStatus, idPhotoUrl) {
      document.getElementById('hiddenUserId').value = String(userId);
      document.getElementById('displayUserName').value = userName;
      document.getElementById('formStatusSelect').value = currentStatus === 'restricted' || currentStatus === 'suspended' ? currentStatus : 'active';
      document.getElementById('modalAdminNotes').value = accountNotes ? String(accountNotes) : '';
      
      document.getElementById('formIdVerifySelect').value = idVerifyStatus || 'unverified';
      
      const photoContainer = document.getElementById('idPhotoContainer');
      const photoLink = document.getElementById('idPhotoLink');
      if (idPhotoUrl) {
         photoContainer.style.display = 'block';
         photoLink.href = idPhotoUrl;
      } else {
         photoContainer.style.display = 'none';
         photoLink.href = '#';
      }

      document.getElementById('userModal').classList.add('active');
    }

    function closeUserModal() {
      document.getElementById('userModal').classList.remove('active');
      const form = document.getElementById('userStatusForm');
      if (form) form.reset();
    }

    document.getElementById('userStatusForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const userId = parseInt(document.getElementById('hiddenUserId').value, 10);
      const account_status = document.getElementById('formStatusSelect').value;
      const admin_notes = document.getElementById('modalAdminNotes').value.trim();
      const id_verification_status = document.getElementById('formIdVerifySelect').value;

      try {
        await window.api.authenticatedFetch('/api/users/update_account_status.php', {
          method: 'POST',
          body: { user_id: userId, account_status, admin_notes, id_verification_status }
        });
        closeUserModal();
        await loadUsers();
      } catch (err) {
        const msg = err?.data?.message || err?.message || 'Unable to update user.';
        alert(msg);
      }
    });

    document.getElementById('userModal').addEventListener('click', (e) => {
      if (e.target.id === 'userModal') closeUserModal();
    });

    function initials(name) {
      const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
      const a = parts[0]?.[0] || 'U';
      const b = parts.length > 1 ? parts[parts.length - 1][0] : '';
      return (a + b).toUpperCase();
    }

    function titleCase(val) {
      const s = String(val || '').trim().toLowerCase();
      return s ? (s.charAt(0).toUpperCase() + s.slice(1)) : 'User';
    }

    function statusPillMarkup(status) {
      const st = String(status || 'active').toLowerCase().trim();
      let cls = 'status-active';
      let label = 'Active';
      if (st === 'restricted') {
        cls = 'status-restricted';
        label = 'Restricted';
      } else if (st === 'suspended') {
        cls = 'status-suspended';
        label = 'Suspended';
      }
      return `<span class="status-pill ${cls}">${label}</span>`;
    }

    function matchesDeptFilter(deptFilterVal, deptText) {
      const v = (deptFilterVal || '').trim();
      if (!v) return true;
      const d = String(deptText || '').toLowerCase();
      if (v === 'coe') return d.includes('engineering');
      if (v === 'cos') return d.includes('science');
      if (v === 'cba') return d.includes('business');
      return true;
    }

    function filteredUsersFromState() {
      const q = (document.getElementById('userSearchInput')?.value || '').trim().toLowerCase();
      const deptF = document.getElementById('deptFilter')?.value || '';
      const statusF = (document.getElementById('statusFilter')?.value || '').trim().toLowerCase();

      return allUsers.filter((u) => {
        // Hide users that haven't been verified yet (they belong in Registration Approvals)
        const idv = String(u.id_verification_status || 'unverified').toLowerCase().trim();
        if (idv === 'unverified' || idv === 'pending') {
            return false; 
        }

        if (statusF && String(u.account_status || 'active').toLowerCase().trim() !== statusF) {
          return false;
        }
        if (!matchesDeptFilter(deptF, u?.department)) {
          return false;
        }
        if (!q) return true;
        const name = String(u?.name || '').toLowerCase();
        const email = String(u?.email || '').toLowerCase();
        const sid = String(u?.school_id || '').toLowerCase();
        return name.includes(q) || email.includes(q) || sid.includes(q);
      });
    }

    function renderUserRows(users) {
      const tbody = document.getElementById('usersTbody');
      if (!tbody) return;

      tbody.innerHTML = users.map((u) => {
        const name = u?.name || '—';
        const email = u?.email || '—';
        const sid = u?.school_id || '—';
        const dept = u?.department || '—';
        const role = titleCase(u?.role);
        const ast = String(u?.account_status || 'active').toLowerCase().trim();
        const idv = String(u?.id_verification_status || 'unverified').toLowerCase().trim();
        
        let idvMarkup = '';
        if (idv === 'verified') {
            idvMarkup = '<span style="color: var(--success); font-size: 11px;"><i class="ph ph-check-circle"></i> Verified</span>';
        } else if (idv === 'pending') {
            idvMarkup = '<span style="color: var(--gold); font-size: 11px;"><i class="ph ph-hourglass"></i> Pending</span>';
        } else {
            idvMarkup = '<span style="color: var(--danger); font-size: 11px;"><i class="ph ph-x-circle"></i> Unverified</span>';
        }

        const isAdminAcct = String(u?.role || '').trim().toLowerCase() === 'admin';
        const manageCell = isAdminAcct
          ? '<span style="color: var(--text-3); font-size: 12px;">—</span>'
          : `<button type="button" class="btn-action btn-manage" data-user-id="${encodeURIComponent(String(u.id))}">
                  <i class="ph ph-sliders-horizontal"></i> Manage
                </button>`;
        return `
            <tr>
              <td>
                <div class="user-cell">
                  <div class="user-avatar-sm">${escapeHtml(initials(name))}</div>
                  <div>
                    <span class="user-name-txt">${escapeHtml(name)}</span>
                    <span class="user-email-txt">${escapeHtml(email)}</span>
                    <div style="margin-top: 2px;">${idvMarkup}</div>
                  </div>
                </div>
              </td>
              <td style="font-family: monospace; color: var(--text-2);">${escapeHtml(sid)}</td>
              <td>${escapeHtml(dept)}</td>
              <td>${escapeHtml(role)}</td>
              <td>${statusPillMarkup(ast)}</td>
              <td style="text-align: right;">${manageCell}</td>
            </tr>
          `;
      }).join('') || `
          <tr>
            <td colspan="6" style="padding: 18px 20px; color: var(--text-3);">No users match the current filters.</td>
          </tr>
        `;
    }

    function applyFilters() {
      renderUserRows(filteredUsersFromState());
    }

    function exportUsersCsv() {
      const rows = filteredUsersFromState();
      const header = ['Name', 'Email', 'School ID', 'Department', 'Role', 'Account status'];
      const lines = [header.join(',')].concat(
        rows.map((u) => {
          const line = [
            u?.name || '',
            u?.email || '',
            u?.school_id || '',
            u?.department || '',
            u?.role || '',
            u?.account_status || 'active'
          ].map((cell) => {
            const s = String(cell ?? '');
            const esc = s.includes('"') || s.includes(',') ? `"${s.replace(/"/g, '""')}"` : s;
            return esc;
          }).join(',');
          return line;
        })
      );
      const blob = new Blob(['\ufeff' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = `hariborrow-users-${new Date().toISOString().slice(0, 10)}.csv`;
      a.click();
      URL.revokeObjectURL(a.href);
    }

    async function loadUsers() {
      const tbody = document.getElementById('usersTbody');
      if (!tbody) return;

      tbody.innerHTML = `
          <tr>
            <td colspan="6" style="padding: 18px 20px; color: var(--text-3);">Loading users…</td>
          </tr>
        `;

      try {
        const res = await window.api.authenticatedFetch('/api/users/list.php');
        allUsers = Array.isArray(res?.users) ? res.users : [];
        applyFilters();
      } catch (e) {
        console.error('Users load failed:', e);
        allUsers = [];
        tbody.innerHTML = `
          <tr>
            <td colspan="6" style="padding: 18px 20px; color: var(--danger);">Failed to load users.</td>
          </tr>
        `;
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      loadUsers();

      ['userSearchInput', 'deptFilter', 'statusFilter'].forEach((id) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', applyFilters);
        el.addEventListener('change', applyFilters);
      });

      const tbody = document.getElementById('usersTbody');
      if (tbody) {
        tbody.addEventListener('click', (e) => {
          const btn = e.target.closest('.btn-manage');
          if (!btn) return;
          const uid = decodeURIComponent(btn.getAttribute('data-user-id') || '');
          const u = allUsers.find((row) => String(row.id) === String(uid));
          if (!u) return;
          openUserModal(
            u.id,
            u.name || 'User',
            u.account_status || 'active',
            u.account_notes || '',
            u.id_verification_status || 'unverified',
            u.id_photo_url || null
          );
        });
      }

      const exp = document.getElementById('exportUsersBtn');
      if (exp) exp.addEventListener('click', exportUsersCsv);
    });
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
                if (badge) {
                    badge.textContent = String(totalPending);
                    badge.style.display = totalPending > 0 ? 'inline-flex' : 'none';
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

</body>

</html>