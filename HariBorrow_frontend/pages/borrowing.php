<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HariBorrow — Asset Catalog</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600&family=Fredoka:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <script src="../js/api.js"></script>
  <script src="../js/auth_guard.js"></script>
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
      --glass-heavy: rgba(10, 10, 13, 0.75);
      --glass-border: rgba(255, 255, 255, 0.08);
      --gold: #E5C07B;
      --gold-light: #FCEBAF;
      --gold-dark: #A68A48;
      --gold-dim: rgba(229, 192, 123, 0.1);
      --gold-glow: rgba(229, 192, 123, 0.25);
      --text-1: #FFFFFF;
      --text-2: #A39E93;
      --text-3: #6B665A;
      --green: #4ade80;
      --green-dim: rgba(74, 222, 128, 0.1);
      --red: #f87171;
      --red-dim: rgba(248, 113, 113, 0.1);
      --blue: #60a5fa;
      --blue-dim: rgba(96, 165, 250, 0.1);
      --success: #4ade80;
      --danger: #ff6b7a;
    }

    html,
    body {
      min-height: 100vh;
      width: 100vw;
      overflow-x: hidden;
      font-family: 'Outfit', sans-serif;
      background-color: var(--bg-deep);
      color: var(--text-1);
    }

    ::-webkit-scrollbar {
      width: 6px;
      background: transparent;
    }

    ::-webkit-scrollbar-thumb {
      background: var(--text-3);
      border-radius: 10px;
    }

    .bg-mesh {
      position: fixed;
      inset: 0;
      z-index: 0;
      background:
        radial-gradient(circle at 15% 50%, rgba(229, 192, 123, 0.05), transparent 40%),
        radial-gradient(circle at 85% 30%, rgba(166, 138, 72, 0.07), transparent 50%),
        var(--bg-deep);
      animation: pulseBg 15s ease-in-out infinite alternate;
    }

    @keyframes pulseBg {
      0% { transform: scale(1); }
      100% { transform: scale(1.05); }
    }

    .ambient-glow {
      position: fixed;
      inset: 0;
      pointer-events: none;
      background: radial-gradient(600px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(229, 192, 123, 0.06), transparent 50%);
      z-index: 9999;
      mix-blend-mode: screen;
    }

    /* ── NAV ── */
    .top-nav {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 80px;
      background: var(--glass-heavy);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border-bottom: 1px solid var(--glass-border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 5%;
      z-index: 100;
    }

    .nav-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
    }

    .nav-logo {
      height: 56px;
      width: auto;
      object-fit: contain;
      filter: drop-shadow(0 0 10px rgba(229, 192, 123, 0.4)) brightness(1.2) contrast(1.3) saturate(1.4);
      transition: transform 0.3s ease, filter 0.3s ease;
    }

    .nav-logo:hover {
      transform: scale(1.05);
      filter: drop-shadow(0 0 18px var(--gold)) brightness(1.4) contrast(1.4) saturate(1.6);
    }

    .nav-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 24px;
      font-weight: 600;
      background: linear-gradient(135deg, #FFF 0%, var(--gold-light) 50%, var(--gold-dark) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      letter-spacing: 0.02em;
    }

    .nav-right {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .nav-back {
      display: flex;
      align-items: center;
      gap: 8px;
      background: transparent;
      border: 1px solid var(--glass-border);
      padding: 8px 16px;
      border-radius: 30px;
      color: var(--text-2);
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      cursor: pointer;
      transition: all 0.3s;
      text-decoration: none;
    }

    .nav-back:hover {
      border-color: var(--gold);
      color: var(--gold);
    }

    .profile-menu {
      position: relative;
    }

    .profile-btn {
      display: flex;
      align-items: center;
      gap: 12px;
      background: transparent;
      border: 1px solid var(--glass-border);
      padding: 8px 16px;
      border-radius: 30px;
      color: var(--text-1);
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .profile-btn:hover {
      border-color: var(--gold);
      background: var(--gold-dim);
    }

    .profile-avatar {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: var(--gold-dark);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      font-size: 12px;
      color: var(--bg-deep);
      overflow: hidden;
    }

    .dropdown {
      position: absolute;
      top: 120%;
      right: 0;
      width: 220px;
      background: rgba(15, 15, 20, 0.85);
      backdrop-filter: blur(24px);
      border: 1px solid var(--glass-border);
      border-radius: 12px;
      padding: 8px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
      opacity: 0;
      pointer-events: none;
      transform: translateY(-10px);
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .dropdown.active {
      opacity: 1;
      pointer-events: auto;
      transform: translateY(0);
    }

    .drop-item {
      display: flex;
      align-items: center;
      gap: 12px;
      width: 100%;
      padding: 12px 16px;
      background: transparent;
      border: none;
      color: var(--text-2);
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      text-align: left;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .drop-item i {
      font-size: 18px;
      color: var(--gold);
      opacity: 0.7;
    }

    .drop-item:hover {
      background: var(--gold-dim);
      color: var(--gold-light);
    }

    .drop-divider {
      height: 1px;
      background: var(--glass-border);
      margin: 6px 0;
    }

    .drop-item.logout { color: #ff6b7a; }
    .drop-item.logout:hover { background: rgba(220, 53, 69, 0.1); }

    /* ── PAGE LAYOUT ── */
    .page {
      position: relative;
      z-index: 10;
      padding: 140px 5% 60px; /* Increased top padding to 120px to match other pages */
      max-width: 1400px;
      margin: 0 auto;
    }

    /* ── PAGE HEADER ── */
    .page-header {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      margin-bottom: 32px;
      padding-bottom: 24px;
      border-bottom: 1px solid var(--glass-border);
      flex-wrap: wrap;
      gap: 16px;
      animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .page-header-left h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 36px;
      font-weight: 500;
      line-height: 1.1;
    }

    .page-header-left p {
      font-size: 13px;
      color: var(--text-2);
      font-weight: 300;
      margin-top: 6px;
    }

    .my-borrows-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      background: var(--glass);
      border: 1px solid var(--glass-border);
      padding: 10px 20px;
      border-radius: 30px;
      color: var(--text-2);
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .my-borrows-btn:hover {
      border-color: var(--gold);
      color: var(--gold);
    }

    .badge-count {
      background: var(--gold);
      color: var(--bg-deep);
      padding: 2px 7px;
      border-radius: 10px;
      font-size: 10px;
      font-weight: 700;
    }

    /* ── TOOLBAR ── */
    .toolbar {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 24px;
      flex-wrap: wrap;
      animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.05s both;
    }

    .search-wrap {
      position: relative;
      flex: 1;
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


    /* ── CATALOG GRID ── */
    .catalog-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 32px; /* Increased gap from 20px to 32px for breathing room */
      animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both;
    }

    .asset-card {
      background: var(--glass);
      backdrop-filter: blur(16px);
      border: 1px solid var(--glass-border);
      border-radius: 16px;
      padding: 32px; /* Expanded padding inside the cards */
      display: flex;
      flex-direction: column;
      gap: 0;
      transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
      position: relative;
      overflow: hidden;
      cursor: pointer;
    }

    .asset-card:hover {
      transform: translateY(-6px);
      border-color: rgba(229, 192, 123, 0.3);
      box-shadow: 0 16px 36px rgba(0, 0, 0, 0.35);
    }

    .card-icon {
      width: 52px;
      height: 52px;
      border-radius: 12px;
      background: var(--gold-dim);
      border: 1px solid rgba(229, 192, 123, 0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 26px;
      color: var(--gold);
      margin-bottom: 18px;
    }

    .card-name {
      font-family: 'Cormorant Garamond', serif;
      font-size: 22px;
      font-weight: 500;
      color: var(--text-1);
      margin-bottom: 4px;
    }

    .card-id {
      font-size: 11px;
      color: var(--text-3);
      margin-bottom: 14px;
    }

    .card-meta {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-bottom: 18px;
    }

    .card-meta-row {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12px;
      color: var(--text-2);
    }

    .card-meta-row i {
      font-size: 15px;
      color: var(--text-3);
      width: 16px;
      flex-shrink: 0;
    }

    .card-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-top: 16px;
      border-top: 1px solid var(--glass-border);
      margin-top: auto;
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 500;
    }

    .badge-available {
      background: var(--green-dim);
      color: var(--green);
      border: 1px solid rgba(74, 222, 128, 0.2);
    }

    .badge-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: currentColor;
    }

    .request-btn {
      display: flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
      border: none;
      padding: 8px 16px;
      border-radius: 20px;
      color: var(--bg-deep);
      font-family: 'Outfit', sans-serif;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    }

    /* ── ADMIN DESIGN INJECT: My Borrowings Panel ── */
    .data-panel {
      display: none;
      background: var(--glass);
      border: 1px solid var(--glass-border);
      border-radius: 12px;
      overflow: hidden;
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      animation: fadeUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .data-panel.active { display: block; }

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

    table { width: 100%; border-collapse: collapse; }
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
    tr:hover td { background: rgba(255, 255, 255, 0.02); }

    .status-pill {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 10px;
      font-weight: 600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      display: inline-block;
    }
    .status-pill.pending { background: rgba(229, 192, 123, 0.1); color: var(--gold); border: 1px solid rgba(229, 192, 123, 0.3); }
    .status-pill.active { background: rgba(74, 222, 128, 0.1); color: var(--success); border: 1px solid rgba(74, 222, 128, 0.3); }
    .status-pill.overdue { background: rgba(255, 107, 122, 0.1); color: var(--danger); border: 1px solid rgba(255, 107, 122, 0.3); }

    /* Buttons inside modals/panels */
    .btn-ghost {
      border: 1px solid var(--glass-border);
      background: transparent;
      color: var(--text-2);
      padding: 10px 14px;
      border-radius: 10px;
      cursor: pointer;
      font-family: 'Outfit', sans-serif;
    }

    .btn-submit {
      border: none;
      background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
      color: var(--bg-deep);
      padding: 10px 14px;
      border-radius: 10px;
      cursor: pointer;
      font-weight: 600;
      font-family: 'Outfit', sans-serif;
    }

    .rating-chip {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 14px;
      border: 1px solid rgba(229, 192, 123, 0.25);
      background: rgba(229, 192, 123, 0.1);
      color: var(--gold-light);
      font-size: 11px;
      font-weight: 500;
    }

    .rate-btn {
      border: 1px solid rgba(96, 165, 250, 0.35);
      background: rgba(96, 165, 250, 0.12);
      color: #93c5fd;
      padding: 7px 12px;
      border-radius: 8px;
      cursor: pointer;
      font-family: 'Outfit', sans-serif;
      font-size: 12px;
      font-weight: 500;
      transition: all 0.2s;
    }

    .rate-btn:hover {
      border-color: #60a5fa;
      background: rgba(96, 165, 250, 0.18);
    }

    /* ── MODAL ── */
    .modal-overlay {
      position: fixed;
      inset: 0;
      z-index: 300;
      background: rgba(0, 0, 0, 0.65);
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .modal-overlay.active { display: flex; }

    .borrow-modal {
      width: min(540px, 96vw);
      background: rgba(15, 15, 20, 0.95);
      border: 1px solid var(--glass-border);
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
    }

    .modal-title { font-size: 20px; color: var(--text-1); margin-bottom: 14px; }
    .modal-sub { color: var(--text-2); font-size: 13px; margin-bottom: 16px; }
    .form-group { margin-bottom: 14px; }
    .form-label { display: block; color: var(--text-2); font-size: 12px; margin-bottom: 6px; }
    .form-input {
      width: 100%; padding: 10px 12px; border-radius: 10px;
      border: 1px solid var(--glass-border); background: rgba(255, 255, 255, 0.03);
      color: var(--text-1); font-family: 'Outfit', sans-serif;
    }

    select.form-input {
      background-color: #12121a;
      cursor: pointer;
    }

    select.form-input option,
    .filter-select option {
      background-color: #1a1a22;
      color: #e2ddd6;
      padding: 8px 12px;
      font-family: 'Outfit', sans-serif;
    }

    select.form-input option:checked,
    .filter-select option:checked {
      background-color: #2a2a35;
      color: var(--gold-light);
    }
    .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px; }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(24px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
  <link rel="stylesheet" href="../css/theme.css">
  <link rel="stylesheet" href="../css/theme.css">
</head>

<body>

  <div class="bg-mesh"></div>
  <div class="ambient-glow" id="glow"></div>

  <nav class="top-nav">
    <a href="borrower_lender_dashboard.php" class="nav-brand">
      <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="HariBorrow Logo" class="nav-logo">
      <span class="nav-title">HariBorrow</span>
    </a>
    <div class="nav-right">
      <a href="borrower_lender_dashboard.php" class="nav-back"><i class="ph ph-arrow-left"></i> Dashboard</a>

      
      <div class="notif-wrapper" style="position:relative;">
          <button class="profile-btn notif-btn" onclick="toggleNotifMenu(event)" style="border-radius: 50%; width: 40px; height: 40px; justify-content: center; padding: 0;">
              <i class="ph ph-bell" style="font-size: 20px;"></i>
              <span class="notif-badge" id="notifBadge" style="display:none; position:absolute; top:-2px; right:-2px; background:var(--danger); color:#fff; font-size:10px; padding:2px 6px; border-radius:10px; font-weight:bold;">0</span>
          </button>
          <div class="dropdown notif-dropdown" id="notifDropdown" style="width: 320px; right: 0;">
              <div style="padding: 12px 16px; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center;">
                  <strong style="color:var(--text-1); font-family: 'Cormorant Garamond', serif; font-size: 16px;">Notifications</strong>
                  <span style="font-size: 12px; cursor: pointer; color: var(--gold);" onclick="markAllNotificationsRead(event)">Mark all read</span>
              </div>
              <div id="notifList" style="max-height: 300px; overflow-y: auto;">
                  <div style="padding: 16px; text-align: center; color: var(--text-3); font-size: 12px;">Loading...</div>
              </div>
          </div>
      </div>

      <div class="profile-menu">
        <button class="profile-btn" onclick="toggleDropdown()">
          <div class="profile-avatar" id="navAvatar">UN</div>
          <span id="navUserName">User Name</span>
          <i class="ph ph-caret-down"></i>
        </button>

        <div class="dropdown" id="settingsDropdown">
          <button class="drop-item" onclick="window.location.href='my_profile.php'"><i class="ph ph-user"></i> My Profile</button>
          <button class="drop-item" onclick="window.location.href='profile_settings.php'"><i class="ph ph-gear"></i> Account Settings</button>
          <div class="drop-divider"></div>
          <button class="drop-item logout" onclick="window.api.removeToken(); window.location.href='login.php'"><i class="ph ph-sign-out"></i> Secure Log Out</button>
        </div>
      </div>
    </div>
  </nav>

  <main class="page">
    <div class="page-header">
      <div class="page-header-left">
        <h1 id="pageTitleText">Asset Catalog</h1>
        <p id="pageDescText">Browse available university equipment and submit borrow requests.</p>
      </div>
      
      <button class="my-borrows-btn" id="btnOpenBorrows" onclick="openBorrows()">
        <i class="ph ph-clock-counter-clockwise"></i>
        My Borrowings
        <span class="badge-count" id="borrowCount">0</span>
      </button>

      <button class="my-borrows-btn" id="btnCloseBorrows" style="display: none;" onclick="closeBorrowsPanel()">
        <i class="ph ph-arrow-left"></i>
        Asset Catalog
      </button>

    </div>

    <div class="toolbar" id="catalogToolbar">
      <div class="search-wrap">
        <i class="ph ph-magnifying-glass"></i>
        <input class="search-input" type="text" placeholder="Search assets by name, type, or ID…"
          oninput="filterAssets(this.value)">
      </div>
      <select class="filter-select" onchange="filterByType(this.value)">
        <option value="">All Types</option>
        <option>Electronics</option>
        <option>Mechanical</option>
        <option>Computing</option>
      </select>
    </div>

    <div class="data-panel" id="borrowsPanel">
      <div class="panel-header" style="flex-direction: column; align-items: flex-start; gap: 16px;">
        <div class="panel-title">Borrowing History</div>
        <div class="role-toggle" style="background: rgba(0,0,0,0.4); border: 1px solid var(--glass-border); border-radius: 40px; padding: 4px; display: inline-flex;">
          <button class="role-btn active" id="tabAll" onclick="switchBorrowTab('all')" style="background: transparent; border: none; padding: 8px 16px; border-radius: 30px; color: var(--gold-light); cursor: pointer; font-family: 'Outfit', sans-serif; font-size: 13px;">All History</button>
          <button class="role-btn" id="tabActive" onclick="switchBorrowTab('active')" style="background: transparent; border: none; padding: 8px 16px; border-radius: 30px; color: var(--text-2); cursor: pointer; font-family: 'Outfit', sans-serif; font-size: 13px;">Active Borrowings</button>
        </div>
      </div>
      <div style="overflow-x:auto;">
        <table>
          <thead>
            <tr>
              <th>Asset</th>
              <th>Counterparty</th>
              <th>Status</th>
              <th>Borrow Date</th>
              <th>Return Due</th>
              <th>Returned At</th>
              <th>Penalty</th>
              <th>Rating</th>
            </tr>
          </thead>
          <tbody id="borrowsTableBody">
            <tr><td colspan="8" style="padding:16px;color:var(--text-3);">Loading your borrowings...</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="catalog-grid" id="catalogGrid">
    </div>
  </main>

  <div class="modal-overlay" id="borrowModal">
    <div class="borrow-modal">
      <div class="modal-title">Submit Borrow Request</div>
      <div class="modal-sub" id="borrowModalAssetName">Asset: —</div>
      <div class="modal-sub" id="borrowModalLenderReputation" style="margin-top:6px;color:var(--text-2);font-size:14px;">Lender reputation: —</div>
      <input type="hidden" id="borrowAssetId">
      <div class="form-group">
        <label class="form-label" for="borrowDateInput">Borrow Date & Time</label>
        <input class="form-input" id="borrowDateInput" type="datetime-local">
      </div>
      <div class="form-group">
        <label class="form-label" for="returnDateInput">Return Date & Time</label>
        <input class="form-input" id="returnDateInput" type="datetime-local">
      </div>
      <div class="modal-actions">
        <button class="btn-ghost" type="button" onclick="closeBorrowModal()">Cancel</button>
        <button class="btn-submit" type="button" onclick="submitBorrowRequest()">Submit Request</button>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="ratingModal">
    <div class="borrow-modal">
      <div class="modal-title">Rate User</div>
      <div class="modal-sub" id="ratingModalCounterparty">Rate your transaction counterpart.</div>
      <input type="hidden" id="ratingTxnId">
      <div class="form-group">
        <label class="form-label" for="ratingValueInput">Rating (1-5)</label>
        <select class="form-input" id="ratingValueInput">
          <option value="5">5 - Excellent</option>
          <option value="4">4 - Good</option>
          <option value="3">3 - Okay</option>
          <option value="2">2 - Poor</option>
          <option value="1">1 - Very Poor</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="ratingCommentInput">Comment (optional)</label>
        <textarea class="form-input" id="ratingCommentInput" rows="3" placeholder="How was your experience?"></textarea>
      </div>
      <div class="modal-actions">
        <button class="btn-ghost" type="button" onclick="closeRatingModal()">Cancel</button>
        <button class="btn-submit" type="button" onclick="submitRating()">Submit Rating</button>
      </div>
    </div>
  </div>

  <script>
    // Ambient Glow Logic
    document.addEventListener('mousemove', e => {
      document.getElementById('glow').style.setProperty('--mouse-x', e.clientX + 'px');
      document.getElementById('glow').style.setProperty('--mouse-y', e.clientY + 'px');
    });

    // Dropdown Logic
    function toggleDropdown() { document.getElementById('settingsDropdown').classList.toggle('active'); }
    document.addEventListener('click', function(event) {
      if (!document.querySelector('.profile-menu').contains(event.target)) {
        document.getElementById('settingsDropdown').classList.remove('active');
      }
    });

    let assets = [];
    let filteredAssets = [];
    let searchQ = '';
    let typeFilter = '';
    let myBorrowings = [];
    let borrowingsById = {};
    let currentBorrowTab = 'all';

    function switchBorrowTab(tab) {
      currentBorrowTab = tab;
      document.getElementById('tabAll').style.color = tab === 'all' ? 'var(--gold-light)' : 'var(--text-2)';
      document.getElementById('tabActive').style.color = tab === 'active' ? 'var(--gold-light)' : 'var(--text-2)';
      renderBorrowsPanel();
    }

    function toInputDateTimeLocal(d) {
      const pad = (n) => String(n).padStart(2, '0');
      return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    /** Build MySQL DATETIME using the picker’s local calendar time (same as borrower chose). */
    function toSqlDateTime(localVal) {
      if (!localVal) return '';
      const d = new Date(localVal);
      if (Number.isNaN(d.getTime())) return '';
      const pad = (n) => String(n).padStart(2, '0');
      return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
    }

    /** Parse API / MySQL "YYYY-MM-DD HH:mm:ss" as local wall-clock (not UTC). */
    function parseMysqlDateTimeLocal(s) {
      if (!s || typeof s !== 'string') return null;
      const m = String(s).trim().match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?/);
      if (!m) return null;
      return new Date(+m[1], +m[2] - 1, +m[3], +m[4], +m[5], +(m[6] || 0));
    }

    function fmtDateSafe(v) {
      if (!v) return '—';
      const d = parseMysqlDateTimeLocal(String(v)) || new Date(v);
      return Number.isNaN(d.getTime()) ? String(v) : d.toLocaleString();
    }

    // New logic to match admin status pills
    function getStatusData(tx) {
      const raw = String(tx?.status || '').toLowerCase();
      if (tx?.dates?.returned) return { label: 'Returned', class: 'active' };
      if (tx?.is_overdue) return { label: 'Overdue', class: 'overdue' };
      if (raw === 'approved' || raw === 'confirmed' || raw === 'active') return { label: 'Active', class: 'active' };
      if (raw === 'rejected') return { label: 'Rejected', class: 'overdue' };
      return { label: 'Pending', class: 'pending' };
    }

    async function loadCatalog() {
      try {
        const response = await window.api.authenticatedFetch('/assets/catalog.php');
        if (response && response.status === 'success') {
          const iconMap = { Electronics: 'ph-wave-sine', Mechanical: 'ph-gear-six', Laboratory: 'ph-flask', Computing: 'ph-cpu' };
          
          assets = response.assets.map(a => {
            return {
              id: a.id,
              name: a.name,
              description: a.description || '',
              type: a.type || 'General',
              lender: a.lender_name || 'System Default',
              lender_rating_count: Number(a.lender_rating_count) || 0,
              lender_rating_average: Number(a.lender_rating_average) || 0,
              status: a.availability,
              meetup_location: a.meetup_location || '',
              daily_penalty: a.daily_penalty || 0,
              penalty_type: a.penalty_type || 'per_day',
              icon: iconMap[a.type] || 'ph-stack'
            };
          });
          
          applyFilters();
        }
      } catch (error) {
        console.error("Failed to load catalog:", error);
      }
    }

    async function loadMyBorrowings() {
      try {
        const res = await window.api.authenticatedFetch('/transactions/history.php');
        const history = Array.isArray(res?.history) ? res.history : [];
        // The API already filters to the current user's transactions and attaches
        // is_current_user_borrower / is_current_user_lender flags.
        myBorrowings = history.sort((a, b) => {
          const aTs = new Date(a?.dates?.borrowed || a?.dates?.requested || 0).getTime() || 0;
          const bTs = new Date(b?.dates?.borrowed || b?.dates?.requested || 0).getTime() || 0;
          return bTs - aTs;
        });

        // Count open borrower-side transactions for the badge
        const openCount = myBorrowings.filter(tx => {
          if (!tx?.is_current_user_borrower) return false;
          const st = String(tx?.status || '').toLowerCase();
          const returned = Boolean(tx?.dates?.returned);
          return ((st === 'approved' || st === 'confirmed' || st === 'active') && !returned) || st === 'pending';
        }).length;
        
        const countEl = document.getElementById('borrowCount');
        if (countEl) countEl.textContent = String(openCount);
        borrowingsById = {};
        myBorrowings.forEach(tx => {
          borrowingsById[String(tx?.transaction_id)] = tx;
        });
      } catch (error) {
        console.error('Failed to load my borrowings:', error);
      }
    }

    function applyFilters() {
        filteredAssets = assets.filter(a => {
            const q = searchQ.toLowerCase();
            const matchQ = !q || a.name.toLowerCase().includes(q) || a.type.toLowerCase().includes(q) || String(a.id).toLowerCase().includes(q);
            const matchT = !typeFilter || a.type === typeFilter;
            return matchQ && matchT;
        });
        renderCatalog();
    }

    function filterAssets(val) { searchQ = val; applyFilters(); }
    function filterByType(val) { typeFilter = val; applyFilters(); }

    function renderCatalog() {
      const grid = document.getElementById('catalogGrid');
      
      if (filteredAssets.length === 0) {
          grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-3);">No assets found in the catalog.</div>';
          return;
      }

      grid.innerHTML = filteredAssets.map(a => {
        const penaltyLabel = a.daily_penalty > 0
          ? `PHP ${a.daily_penalty} / ${a.penalty_type === 'per_hour' ? 'hour' : 'day'}`
          : 'No penalty';
        const locationHtml = a.meetup_location
          ? `<div class="card-meta-row"><i class="ph ph-map-pin"></i> ${a.meetup_location}</div>`
          : '';
        const lenderRep = (a.lender_rating_count > 0)
          ? `<div class="card-meta-row"><i class="ph ph-star"></i> Lender ${Number(a.lender_rating_average).toFixed(2)} ★ (${a.lender_rating_count})</div>`
          : `<div class="card-meta-row" style="opacity:0.85;"><i class="ph ph-star"></i> Lender: no ratings yet</div>`;
        return `
        <div class="asset-card">
          <div class="card-icon"><i class="ph ${a.icon}"></i></div>
          <div class="card-name">${a.name}</div>
          <div class="card-id">ID: ${a.id}</div>
          <div class="card-meta">
            <div class="card-meta-row"><i class="ph ph-tag"></i> ${a.type}</div>
            <div class="card-meta-row"><i class="ph ph-user"></i> ${a.lender}</div>
            ${lenderRep}
            ${locationHtml}
            <div class="card-meta-row"><i class="ph ph-warning-circle"></i> ${penaltyLabel}</div>
          </div>
          <div class="card-footer">
            <span class="badge ${a.status === 'available' ? 'badge-available' : 'badge-borrowed'}"><span class="badge-dot"></span>${a.status === 'available' ? 'Available' : 'Unavailable'}</span>
            <button class="request-btn" ${a.status !== 'available' ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : ''} onclick='openBorrowModal(${JSON.stringify(String(a.id))}, ${JSON.stringify(String(a.name))})'><i class="ph ph-paper-plane-tilt"></i> Request</button>
          </div>
        </div>
      `}).join('');
    }

    function openBorrowModal(id, assetName) {
      document.getElementById('borrowAssetId').value = id;
      document.getElementById('borrowModalAssetName').textContent = `Asset: ${assetName || '—'}`;
      const list = filteredAssets && filteredAssets.length ? filteredAssets : assets;
      const asset = list.find(x => String(x.id) === String(id));
      const repEl = document.getElementById('borrowModalLenderReputation');
      if (repEl) {
        const cnt = asset ? Number(asset.lender_rating_count) || 0 : 0;
        const avg = asset ? Number(asset.lender_rating_average) || 0 : 0;
        const lenderName = asset && asset.lender ? asset.lender : 'Lender';
        repEl.textContent = cnt > 0
          ? `${lenderName} — ${avg.toFixed(2)} ★ average from ${cnt} rating${cnt === 1 ? '' : 's'}`
          : `${lenderName} — no ratings yet (new or unrated lender)`;
      }
      const now = new Date();
      const plus3 = new Date(now.getTime() + (3 * 24 * 60 * 60 * 1000));
      document.getElementById('borrowDateInput').value = toInputDateTimeLocal(now);
      document.getElementById('returnDateInput').value = toInputDateTimeLocal(plus3);
      document.getElementById('borrowModal').classList.add('active');
    }

    function closeBorrowModal() {
      document.getElementById('borrowModal').classList.remove('active');
    }

    async function requestAsset(id, borrowDateSql, returnDateSql) {
        try {
          const res = await window.api.authenticatedFetch('/transactions/request.php', {
            method: 'POST',
            body: { asset_id: id, borrow_date: borrowDateSql, return_date: returnDateSql }
          });
          alert(res?.message || 'Borrow request submitted.');
          closeBorrowModal();
          await loadCatalog();
          await loadMyBorrowings();
        } catch (e) {
          alert(e?.message || 'Failed to submit borrow request.');
        }
    }

    async function submitBorrowRequest() {
      const id = document.getElementById('borrowAssetId').value;
      const borrowRaw = document.getElementById('borrowDateInput').value;
      const returnRaw = document.getElementById('returnDateInput').value;
      if (!id) { alert('Asset is required.'); return; }
      if (!borrowRaw || !returnRaw) { alert('Borrow date and return date are required.'); return; }
      const borrowDate = new Date(borrowRaw);
      const returnDate = new Date(returnRaw);
      if (Number.isNaN(borrowDate.getTime()) || Number.isNaN(returnDate.getTime()) || returnDate <= borrowDate) {
        alert('Invalid dates. Return date must be later than borrow date.');
        return;
      }
      await requestAsset(id, toSqlDateTime(borrowRaw), toSqlDateTime(returnRaw));
    }

    // Toggle Views
    function openBorrows() {
      const panel = document.getElementById('borrowsPanel');
      const grid = document.getElementById('catalogGrid');
      const toolbar = document.getElementById('catalogToolbar');
      
      if (panel) panel.classList.add('active');
      if (grid) grid.style.display = 'none';
      if (toolbar) toolbar.style.display = 'none';

      // Update Text and Buttons
      document.getElementById('pageTitleText').textContent = 'My Borrowings';
      document.getElementById('pageDescText').textContent = 'Track and manage your asset requests and history.';
      document.getElementById('btnOpenBorrows').style.display = 'none';
      document.getElementById('btnCloseBorrows').style.display = 'flex';
      
      renderBorrowsPanel();
    }

    function closeBorrowsPanel() {
      const panel = document.getElementById('borrowsPanel');
      const grid = document.getElementById('catalogGrid');
      const toolbar = document.getElementById('catalogToolbar');
      
      if (panel) panel.classList.remove('active');
      if (grid) grid.style.display = '';
      if (toolbar) toolbar.style.display = 'flex';

      // Restore Text and Buttons
      document.getElementById('pageTitleText').textContent = 'Asset Catalog';
      document.getElementById('pageDescText').textContent = 'Browse available university equipment and submit borrow requests.';
      document.getElementById('btnOpenBorrows').style.display = 'flex';
      document.getElementById('btnCloseBorrows').style.display = 'none';
    }

    function renderBorrowsPanel() {
      const tbody = document.getElementById('borrowsTableBody');
      if (!tbody) return;

      // Only show transactions where the current user is the borrower
      let filteredHistory = myBorrowings.filter(tx => tx?.is_current_user_borrower === true);

      if (currentBorrowTab === 'active') {
        filteredHistory = filteredHistory.filter(tx => {
          const st = String(tx?.status || '').toLowerCase();
          const returned = Boolean(tx?.dates?.returned);
          return ((st === 'approved' || st === 'confirmed' || st === 'active') && !returned) || st === 'pending' || (st === 'overdue' && !returned);
        });
      }

      if (!filteredHistory.length) {
        tbody.innerHTML = '<tr><td colspan="8" style="padding: 24px; color: var(--text-3); text-align: center;">You have no ' + (currentBorrowTab === 'active' ? 'active borrowings' : 'borrowing history') + ' yet.</td></tr>';
        return;
      }

      tbody.innerHTML = filteredHistory.map(tx => {
        const statusData = getStatusData(tx);
        const item = tx?.asset?.name || `Asset #${tx?.asset?.id || '—'}`;
        const borrowedAt = tx?.dates?.borrowed || tx?.dates?.requested || null;
        const dueAt = tx?.dates?.due || null;
        const returnedAt = tx?.dates?.returned || null;
        const penalty = Number(tx?.penalty_amount || 0);
        const counterpartyName = tx?.counterparty?.name || '—';
        const myRating = tx?.my_rating?.rating ? Number(tx.my_rating.rating) : null;
        const ratingCell = myRating
          ? `<span class="rating-chip"><i class="ph ph-star-fill"></i> ${myRating}/5</span>`
          : (tx?.can_rate
              ? `<button class="rate-btn" onclick="openRatingModal(${Number(tx?.transaction_id || 0)})"><i class="ph ph-star"></i> Rate</button>`
              : '—');
        return `
          <tr>
            <td style="color: var(--text-1); font-weight: 500;">${item}</td>
            <td>${counterpartyName}</td>
            <td><span class="status-pill ${statusData.class}">${statusData.label}</span></td>
            <td>${fmtDateSafe(borrowedAt)}</td>
            <td>${fmtDateSafe(dueAt)}</td>
            <td>${fmtDateSafe(returnedAt)}</td>
            <td style="color: ${penalty > 0 ? 'var(--danger)' : 'inherit'}">${penalty > 0 ? 'PHP ' + penalty : '—'}</td>
            <td>${ratingCell}</td>
          </tr>
        `;
      }).join('');
    }

    function openRatingModal(transactionId) {
      const tx = borrowingsById[String(transactionId)];
      if (!tx) return;
      document.getElementById('ratingTxnId').value = String(transactionId);
      document.getElementById('ratingCommentInput').value = '';
      document.getElementById('ratingValueInput').value = '5';
      document.getElementById('ratingModalCounterparty').textContent = `Rate ${tx?.counterparty?.name || 'this user'} for transaction #TXN-${transactionId}.`;
      document.getElementById('ratingModal').classList.add('active');
    }

    function closeRatingModal() {
      document.getElementById('ratingModal').classList.remove('active');
    }

    async function submitRating() {
      const transactionId = Number(document.getElementById('ratingTxnId').value || 0);
      const rating = Number(document.getElementById('ratingValueInput').value || 0);
      const reviewText = document.getElementById('ratingCommentInput').value || '';
      if (!transactionId || rating < 1 || rating > 5) {
        alert('Please provide a valid rating.');
        return;
      }
      try {
        const res = await window.api.authenticatedFetch('/transactions/rate.php', {
          method: 'POST',
          body: {
            transaction_id: transactionId,
            rating,
            review_text: reviewText
          }
        });
        alert(res?.message || 'Rating submitted.');
        closeRatingModal();
        await loadMyBorrowings();
        if (document.getElementById('borrowsPanel')?.classList.contains('active')) {
          renderBorrowsPanel();
        }
      } catch (e) {
        alert(e?.message || 'Failed to submit rating.');
      }
    }

    // Initialize User Information
    document.addEventListener('DOMContentLoaded', async () => {
        document.getElementById('borrowModal')?.addEventListener('click', (e) => {
          if (e.target.id === 'borrowModal') closeBorrowModal();
        });
        document.getElementById('ratingModal')?.addEventListener('click', (e) => {
          if (e.target.id === 'ratingModal') closeRatingModal();
        });
        
        await loadCatalog();
        await loadMyBorrowings();
        
        // Support deep-link from dashboard "My Borrowings" card.
        const params = new URLSearchParams(window.location.search);
        if ((params.get('view') || '').toLowerCase() === 'borrows') {
          openBorrows();
        }
        
        // Keep My Borrowings count/status fresh
        setInterval(async () => {
          await loadMyBorrowings();
          if (document.getElementById('borrowsPanel')?.classList.contains('active')) {
            renderBorrowsPanel();
          }
        }, 15000);
    });

  </script>


  <script>
  function toggleNotifMenu(e) {
      if(e) e.stopPropagation();
      document.getElementById('notifDropdown')?.classList.toggle('active');
  }

  document.addEventListener('click', function (event) {
      if (!event.target.closest('.notif-wrapper')) {
          document.getElementById('notifDropdown')?.classList.remove('active');
      }
  });

  async function fetchNotifications() {
      try {
          const response = await window.api.authenticatedFetch('/transactions/notifications.php');
          if (response && response.status === 'success') {
              const notifs = response.notifications;
              const notifList = document.getElementById('notifList');
              const notifBadge = document.getElementById('notifBadge');
              if(!notifList) return;
              
              notifList.innerHTML = '';
              
              if (notifs.length > 0) {
                  const unreadCount = notifs.filter(n => n.notification_id && !n.is_read).length;
                  notifBadge.style.display = unreadCount > 0 ? 'flex' : 'none';
                  notifBadge.textContent = unreadCount > 9 ? '9+' : unreadCount;
                  
                  notifs.forEach(notif => {
                      const date = new Date(notif.time_ago.replace(' ', 'T'));
                      const timeAgo = date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                      
                      const item = document.createElement('div');
                      item.style.padding = '12px 16px';
                      item.style.borderBottom = '1px solid var(--glass-border)';
                      item.style.cursor = notif.is_read ? 'default' : 'pointer';
                      item.style.opacity = notif.is_read ? '0.6' : '1';
                      item.innerHTML = `
                          <div style="display:flex; gap:10px;">
                              <div style="flex:1;">
                                  <div style="font-size:13px; font-weight:500; color:var(--text-1);">${notif.title}</div>
                                  <div style="font-size:12px; color:var(--text-2); margin-top:4px;">${notif.message}</div>
                                  <div style="font-size:11px; color:var(--text-3); margin-top:6px;">${timeAgo}</div>
                              </div>
                          </div>
                      `;
                      if (notif.notification_id && !notif.is_read) {
                          item.addEventListener('click', async (e) => {
                              e.preventDefault();
                              await markNotificationRead(notif.notification_id);
                          });
                      }
                      notifList.appendChild(item);
                  });
              } else {
                  notifBadge.style.display = 'none';
                  notifList.innerHTML = '<div style="padding: 16px; text-align: center; color: var(--text-3); font-size: 12px;">No new notifications.</div>';
              }
          }
      } catch (error) {
          console.error("Failed to fetch notifications:", error);
      }
  }

  async function markNotificationRead(notificationId) {
      try {
          await window.api.authenticatedFetch('/transactions/notifications_mark_read.php', {
              method: 'PUT',
              body: { notification_id: notificationId }
          });
          await fetchNotifications();
      } catch (error) {
          console.error("Failed to mark notification read:", error);
      }
  }

  async function markAllNotificationsRead(event) {
      if (event) event.stopPropagation();
      try {
          await window.api.authenticatedFetch('/transactions/notifications_mark_read.php', {
              method: 'PUT',
              body: {}
          });
          await fetchNotifications();
      } catch (error) {
          console.error("Failed to mark all notifications read:", error);
      }
  }
  
  document.addEventListener('DOMContentLoaded', () => {
      fetchNotifications();
      setInterval(fetchNotifications, 15000);
  });
  </script>
  <script src="../js/theme.js"></script>

</body>

</html>
