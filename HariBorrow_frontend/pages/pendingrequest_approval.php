<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HariBorrow — Pending Requests</title>
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
            --danger-dim: rgba(255, 107, 122, 0.1);
            --red-dim: rgba(248, 113, 113, 0.1);
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
            background: radial-gradient(600px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(229, 192, 123, 0.04), transparent 50%);
            z-index: 9999;
            mix-blend-mode: screen;
            transition: background 0.1s;
        }

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
            filter: drop-shadow(0 0 10px rgba(229, 192, 123, 0.4)) brightness(1.2) contrast(1.3) saturate(1.4);
            transition: transform 0.3s ease, filter 0.3s ease;
        }

        .nav-logo:hover {
            transform: scale(1.05);
            filter: drop-shadow(0 0 18px var(--gold)) brightness(1.4) contrast(1.4) saturate(1.6);
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

        .toolbar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .search-wrap {
            position: relative;
            flex: 1;
            min-width: 220px;
            max-width: 400px;
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

        .search-input::placeholder {
            color: var(--text-3);
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

        .data-panel {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            overflow: hidden;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--glass-border);
            color: var(--text-2);
            padding: 6px 14px;
            border-radius: 6px;
            font-family: 'Outfit', sans-serif;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-outline:hover {
            border-color: var(--gold);
            color: var(--gold);
            background: var(--gold-dim);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
            border: none;
            color: var(--bg-deep);
            font-weight: 600;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px var(--gold-glow);
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

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            z-index: 200;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 20px;
            overflow-y: auto;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal {
            background: rgba(15, 15, 20, 0.97);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 0;
            width: 100%;
            max-width: 600px;
            margin: auto;
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 40px);
            transform: scale(0.95);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }

        .modal-overlay.active .modal {
            transform: scale(1);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 28px 32px 16px;
            border-bottom: 1px solid var(--glass-border);
            flex-shrink: 0;
        }

        .modal-body {
            padding: 24px 32px;
            overflow-y: auto;
            flex: 1;
        }

        .modal-body::-webkit-scrollbar { width: 4px; }
        .modal-body::-webkit-scrollbar-thumb { background: var(--glass-border); border-radius: 4px; }

        .modal-footer {
            padding: 16px 32px 28px;
            border-top: 1px solid var(--glass-border);
            background: rgba(15, 15, 20, 0.97);
            flex-shrink: 0;
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
            background: var(--red-dim);
            border-color: rgba(248, 113, 113, 0.3);
            color: var(--danger);
        }

        .modal-actions {
            display: flex;
            gap: 12px;
        }

        .btn-danger {
            background: transparent;
            border: 1px solid rgba(255, 107, 122, 0.4);
            color: var(--danger);
            padding: 6px 14px;
            border-radius: 6px;
            font-family: 'Outfit', sans-serif;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-danger:hover { background: var(--danger-dim); border-color: var(--danger); }

        .modal-error {
            display: none;
            align-items: center;
            gap: 10px;
            background: rgba(255, 107, 122, 0.1);
            border: 1px solid rgba(255, 107, 122, 0.35);
            border-radius: 10px;
            padding: 12px 16px;
            color: var(--danger);
            font-size: 13px;
            margin-bottom: 12px;
            animation: fadeUp 0.3s ease both;
        }
        .modal-error.visible { display: flex; }

        @media (max-width: 600px) {
            .modal-overlay { padding: 0; align-items: flex-end; }
            .modal { max-width: 100%; border-radius: 20px 20px 0 0; max-height: 92vh; margin: 0; }
            .modal-header { padding: 20px 20px 14px; }
            .modal-body { padding: 16px 20px; }
            .modal-footer { padding: 12px 20px 20px; }
            .review-grid { grid-template-columns: 1fr; }
            .modal-actions { flex-direction: column; }
        }

        .review-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .review-section {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 16px;
        }

        .review-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--text-3);
            margin-bottom: 12px;
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 8px;
        }

        .review-data {
            margin-bottom: 12px;
        }

        .review-data span {
            display: block;
            font-size: 11px;
            color: var(--text-3);
            margin-bottom: 2px;
        }

        .review-data strong {
            display: block;
            font-size: 14px;
            color: var(--text-1);
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-textarea {
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
            resize: vertical;
            min-height: 80px;
        }

        .form-textarea:focus {
            border-color: var(--gold);
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            border-top: 1px solid var(--glass-border);
            padding-top: 24px;
        }

        .btn-approve {
            flex: 1;
            background: var(--success);
            border: none;
            padding: 14px;
            border-radius: 10px;
            color: var(--bg-deep);
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .btn-approve:hover {
            background: #22c55e;
            box-shadow: 0 4px 15px rgba(74, 222, 128, 0.3);
        }

        .btn-deny {
            flex: 1;
            background: transparent;
            border: 1px solid rgba(255, 107, 122, 0.4);
            padding: 14px;
            border-radius: 10px;
            color: var(--danger);
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .btn-deny:hover {
            background: rgba(255, 107, 122, 0.1);
            border-color: var(--danger);
        }

        .filter-select option {
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
  <link rel="stylesheet" href="../css/theme.css">
  <link rel="stylesheet" href="../css/theme.css">
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
            <a href="pendingrequest_approval.php" class="nav-link active" id="pendingNavLink"><i class="ph ph-bell-ringing"></i> Pending Requests
                <span class="nav-badge" id="pendingNavBadge" style="display:none;">0</span>
            </a>
            <a href="active_transactions.php" class="nav-link"><i class="ph ph-clock-counter-clockwise"></i> All Transactions</a>

            <div class="nav-section-title">Database</div>
            <a href="asset_inventory.php" class="nav-link"><i class="ph ph-stack"></i> Asset Inventory</a>
            <a href="registered_users.php" class="nav-link"><i class="ph ph-users"></i> Registered Users</a>

            <div class="nav-section-title">System</div>
            <a href="registration_approval.php" class="nav-link"><i class="ph ph-shield-check"></i> Registration Approvals</a>
            <a href="system_logs.php" class="nav-link"><i class="ph ph-file-text"></i> System Logs</a>
        </nav>

        <div class="sidebar-footer">
            <div class="admin-profile">
                <div class="admin-avatar" id="sidebarAvatar">UN</div>
                <div class="admin-info">
                    <span class="admin-name" id="sidebarName">Admin Name</span>
                    <span class="admin-role" id="sidebarRole">Admin</span>
                </div>
                <a href="javascript:void(0);" onclick="window.api.removeToken(); window.location.href='login.php'" style="margin-left: auto; color: var(--text-3); cursor: pointer;"><i
                        class="ph ph-sign-out" style="font-size: 20px;"></i></a>
            </div>
        </div>
    </aside>

    <main class="main-content">

        <div class="header-area">
            <div class="page-title">
                <h1>Pending Requests</h1>
                <p>Review and process incoming equipment loan applications.</p>
            </div>
        </div>

        <div class="toolbar">
            <div class="search-wrap">
                <i class="ph ph-magnifying-glass"></i>
                <input class="search-input" type="text" id="pendingSearch" name="search_query"
                    placeholder="Search by Transaction ID or Borrower...">
            </div>

            <select class="filter-select" id="pendingDepartmentFilter" name="department_filter">
                <option value="">All Departments</option>
                <option value="COE">College of Engineering (COE)</option>
                <option value="COS">College of Science (COS)</option>
                <option value="CA">College of Architecture (CA)</option>
                <option value="CON">College of Nursing (CON)</option>
                <option value="CBA">College of Business Admin (CBA)</option>
            </select>
        </div>

        <div class="data-panel">
            <table>
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Borrower</th>
                        <th>Asset Requested</th>
                        <th>Location</th>
                        <th>Duration / Schedule</th>
                        <th>Proposed Penalty</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="pendingRequestsBody"></tbody>
            </table>
        </div>

        <div class="data-panel" style="margin-top:20px;">
            <table>
                <thead>
                    <tr>
                        <th>Pending Asset ID</th>
                        <th>Asset</th>
                        <th>Lender</th>
                        <th>Photo</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="pendingAssetsBody"></tbody>
            </table>
        </div>

    </main>

    <div class="modal-overlay" id="reviewModal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title" style="color: var(--text-1);">
                    <i class="ph ph-file-text" style="color: var(--gold);"></i>
                    Review Request <span id="modalTxnId"
                        style="font-family: monospace; font-size: 20px; color: var(--text-3); margin-left: 8px;"></span>
                </div>
                <button class="modal-close" onclick="closeReviewModal()"><i class="ph ph-x"></i></button>
            </div>

            <div class="modal-body">
                <div class="review-grid">
                    <div class="review-section">
                        <div class="review-label">Borrower Information</div>
                        <div class="review-data"><span>Name</span><strong id="modalBorrowerName">—</strong></div>
                        <div class="review-data"><span>School ID</span><strong id="modalSchoolId">—</strong></div>
                        <div class="review-data"><span>Email</span><strong id="modalEmail">—</strong></div>
                    </div>

                    <div class="review-section">
                        <div class="review-label">Fulfillment Details</div>
                        <div class="review-data"><span>Asset</span><strong id="modalAsset">—</strong></div>
                        <div id="modalAssetImageWrap" style="display:none; margin-bottom:12px;">
                            <img id="modalAssetImage" src="" alt="Asset photo" style="width:100%;max-height:160px;object-fit:cover;border-radius:10px;border:1px solid var(--glass-border);">
                        </div>
                        <div class="review-data"><span>Designated Location</span><strong id="modalLocation" style="color: var(--gold);">—</strong></div>
                        <div class="review-data"><span>Schedule</span><strong id="modalSchedule">—</strong></div>
                        <div class="review-data"><span>Duration / Penalty</span><strong id="modalDuration">—</strong></div>
                    </div>
                </div>

                <form id="decisionForm" onsubmit="event.preventDefault();">
                    <input type="hidden" name="transaction_id" id="hiddenTxnId" value="">
                    <input type="hidden" name="admin_decision" id="hiddenDecision" value="">

                    <div class="form-group" style="margin-top: 8px;">
                        <span class="review-label" style="border: none; padding: 0; margin-bottom: 8px; display:block;">Admin Remarks (Optional)</span>
                        <textarea class="form-textarea" name="admin_remarks"
                            placeholder="Add notes before approving or denying..."></textarea>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <div class="modal-error" id="modalError">
                    <i class="ph ph-warning-circle" style="font-size:18px; flex-shrink:0;"></i>
                    <span id="modalErrorMsg">An error occurred. Please try again.</span>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-deny" onclick="processAction(this, 'Denied')"><i
                            class="ph ph-x-circle"></i> Deny Request</button>
                    <button type="button" class="btn-approve" onclick="processAction(this, 'Approved')"><i
                            class="ph ph-check-circle"></i> Approve Request</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const glow = document.getElementById('glow');
        document.addEventListener('mousemove', (e) => {
            glow.style.setProperty('--mouse-x', e.clientX + 'px');
            glow.style.setProperty('--mouse-y', e.clientY + 'px');
        });

        let allPendingRequests = [];
        let allPendingAssets = [];

        function esc(v) {
            return String(v ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        function fmtDateTime(val) {
            if (!val) return '—';
            const d = new Date(val);
            if (Number.isNaN(d.getTime())) return String(val);
            return d.toLocaleString();
        }

        function renderPendingRequests() {
            const tbody = document.getElementById('pendingRequestsBody');
            if (!tbody) return;
            const q = (document.getElementById('pendingSearch')?.value || '').trim().toLowerCase();
            const dept = (document.getElementById('pendingDepartmentFilter')?.value || '').trim().toLowerCase();

            const filtered = allPendingRequests.filter(tx => {
                const school = String(tx?.borrower?.school_id || '').toLowerCase();
                const name = String(tx?.borrower?.name || '').toLowerCase();
                const asset = String(tx?.asset?.name || '').toLowerCase();
                const blob = `${tx?.transaction_id} ${name} ${school} ${asset}`.toLowerCase();
                return (!q || blob.includes(q)) && (!dept || school.includes(dept) || name.includes(dept));
            });

            tbody.innerHTML = filtered.map(tx => {
                const txId = tx?.transaction_id ?? '';
                const borrower = tx?.borrower?.name || '—';
                const school = tx?.borrower?.school_id || '—';
                const asset = tx?.asset?.name || '—';
                const meetupLocation = tx?.asset?.meetup_location || '—';
                const proposedPenalty = Number(tx?.asset?.proposed_penalty_amount || tx?.asset?.daily_penalty || 0);
                const borrowDate = fmtDateTime(tx?.dates?.borrowed || tx?.dates?.requested);
                const returnDate = fmtDateTime(tx?.dates?.due);
                return `
                    <tr>
                        <td style="color: var(--text-1); font-family: monospace;">#TXN-${esc(txId)}</td>
                        <td>${esc(borrower)} <br><span style="font-size: 11px; color: var(--text-3);">${esc(school)}</span></td>
                        <td style="color: var(--text-1);">${esc(asset)}</td>
                        <td>${esc(meetupLocation)}</td>
                        <td style="white-space: nowrap;">
                            <span style="color: var(--gold); font-weight: 500;">Borrow: ${esc(borrowDate)}</span><br>
                            <span style="font-size: 11px; color: var(--text-3);">Return: ${esc(returnDate)}</span>
                        </td>
                        <td>PHP ${proposedPenalty.toFixed(2)}</td>
                        <td><span class="status-pill pending">Pending</span></td>
                        <td>
                            <button class="btn-outline btn-primary"
                                onclick='openReviewModal(${JSON.stringify(txId)}, ${JSON.stringify(meetupLocation)}, ${JSON.stringify(borrowDate)}, ${JSON.stringify(returnDate)}, ${JSON.stringify(`PHP ${proposedPenalty.toFixed(2)}`)})'>
                                <i class="ph ph-magnifying-glass-plus"></i> Review
                            </button>
                        </td>
                    </tr>
                `;
            }).join('') || `<tr><td colspan="8" style="padding:18px; color: var(--text-3);">No pending requests found.</td></tr>`;
        }

        async function loadPendingRequests() {
            try {
                const res = await window.api.authenticatedFetch('/transactions/history.php');
                const history = Array.isArray(res?.history) ? res.history : [];
                allPendingRequests = history.filter(tx => String(tx?.status || '').toLowerCase() === 'pending');
                renderPendingRequests();
                updatePendingBadge();
            } catch (e) {
                console.error('Failed to load pending requests', e);
                const tbody = document.getElementById('pendingRequestsBody');
                if (tbody) tbody.innerHTML = '<tr><td colspan="7" style="padding:18px; color: var(--danger);">Failed to load pending requests.</td></tr>';
            }
        }

        async function loadPendingAssets() {
            try {
                const res = await window.api.authenticatedFetch('/assets/admin_approval.php');
                const pending = Array.isArray(res?.pending_assets) ? res.pending_assets : [];
                allPendingAssets = pending;
                const tbody = document.getElementById('pendingAssetsBody');
                if (!tbody) return;
                tbody.innerHTML = pending.map(a => {
                    const imgHtml = a.asset_image
                        ? `<img src="${esc(a.asset_image)}" alt="${esc(a.name)}" style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid var(--glass-border);">`
                        : `<div style="width:48px;height:48px;border-radius:8px;background:var(--gold-dim);display:flex;align-items:center;justify-content:center;border:1px solid rgba(229,192,123,0.15);"><i class="ph ph-image" style="font-size:20px;color:var(--gold-dark);"></i></div>`;
                    return `
                    <tr>
                        <td style="color: var(--text-1); font-family: monospace;">#AST-${esc(a.id)}</td>
                        <td style="color: var(--text-1);">${esc(a.name || '—')}</td>
                        <td>${esc(a.lender_name || '—')}</td>
                        <td>${imgHtml}</td>
                        <td>${esc(fmtDateTime(a.created_at))}</td>
                        <td><span class="status-pill pending">${esc(a.status || 'pending')}</span></td>
                        <td>
                            <button class="btn-outline btn-primary" onclick="approvePendingAsset(${Number(a.id)})">
                                <i class="ph ph-check"></i> Approve
                            </button>
                            <button class="btn-outline btn-danger" style="margin-left:8px;" onclick="rejectPendingAsset(${Number(a.id)})">
                                <i class="ph ph-x"></i> Reject
                            </button>
                        </td>
                    </tr>
                `}).join('') || `<tr><td colspan="7" style="padding:18px; color: var(--text-3);">No pending assets found.</td></tr>`;
                updatePendingBadge();
            } catch (e) {
                console.error('Failed to load pending assets', e);
                const tbody = document.getElementById('pendingAssetsBody');
                if (tbody) tbody.innerHTML = '<tr><td colspan="6" style="padding:18px; color: var(--danger);">Failed to load pending assets.</td></tr>';
            }
        }

        function updatePendingBadge() {
            const txCount = allPendingRequests.length;
            const total = txCount + allPendingAssets.length;
            const badge = document.getElementById('pendingNavBadge');
            const pendingLink = document.getElementById('pendingNavLink');
            if (badge) {
                badge.textContent = String(total);
                badge.style.display = total > 0 ? 'inline-flex' : 'none';
            }
            if (pendingLink) {
                if (total > 0) {
                    pendingLink.classList.add('has-notif');
                } else {
                    pendingLink.classList.remove('has-notif');
                }
            }
        }

        async function approvePendingAsset(assetId) {
            try {
                await window.api.authenticatedFetch('/assets/admin_approval.php', {
                    method: 'POST',
                    body: { id: assetId, status: 'approved' }
                });
                await loadPendingAssets();
            } catch (e) {
                alert(e?.message || 'Failed to approve asset.');
            }
        }

        async function rejectPendingAsset(assetId) {
            try {
                await window.api.authenticatedFetch('/assets/admin_approval.php', {
                    method: 'POST',
                    body: { id: assetId, status: 'rejected' }
                });
                await loadPendingAssets();
            } catch (e) {
                alert(e?.message || 'Failed to reject asset.');
            }
        }

        document.addEventListener('DOMContentLoaded', async () => {
            let user = window.api.getUser();
            
            if (!user || !user.name) {
                try {
                    const data = await window.api.authenticatedFetch('/users/profile.php');
                    if (data && data.status === 'success') {
                        user = {
                            id: data.profile.id,
                            name: data.profile.full_name,
                            email: data.profile.email,
                            role: data.profile.role,
                            profile_picture: data.profile.profile_picture
                        };
                        window.api.setUser(user);
                    }
                } catch (e) { console.error('Failed to fetch profile', e); }
            }

            if (user && user.name) {
                const safeName = String(user.name).trim();
                document.getElementById('sidebarName').textContent = safeName;
                const rawRole = (user.role || '').trim().toLowerCase();
                document.getElementById('sidebarRole').textContent = rawRole ? (rawRole.charAt(0).toUpperCase() + rawRole.slice(1)) : 'Admin';
                const avatarEl = document.getElementById('sidebarAvatar');
                if (avatarEl) {
                    if (user.profile_picture) {
                        avatarEl.innerHTML = `<img src="${user.profile_picture}" alt="Profile" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
                    } else {
                        const parts = safeName.split(/\s+/).filter(Boolean);
                        const initials = ((parts[0] ? parts[0][0] : 'A') + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase();
                        avatarEl.textContent = initials;
                    }
                }
            }

            await loadPendingRequests();
            await loadPendingAssets();
            document.getElementById('pendingSearch')?.addEventListener('input', renderPendingRequests);
            document.getElementById('pendingDepartmentFilter')?.addEventListener('change', renderPendingRequests);
            // Live refresh so new uploads appear without manual reload.
            setInterval(async () => {
                await loadPendingRequests();
                await loadPendingAssets();
            }, 15000);
        });

        const links = document.querySelectorAll('.nav-link');
        links.forEach(link => {
            link.addEventListener('click', function (e) {
                links.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });

        function openReviewModal(txnId, location, startDate, endDate, duration) {
            const req = (allPendingRequests || []).find(tx => String(tx?.transaction_id) === String(txnId));

            document.getElementById('modalTxnId').textContent = '#TXN-' + txnId;
            document.getElementById('hiddenTxnId').value = txnId;

            // Borrower info
            if (req) {
                document.getElementById('modalBorrowerName').textContent = req?.borrower?.name || '—';
                document.getElementById('modalSchoolId').textContent = req?.borrower?.school_id || '—';
                document.getElementById('modalEmail').textContent = req?.borrower?.email || '—';
                document.getElementById('modalAsset').textContent = req?.asset?.name || '—';

                // Show asset image if available
                const imgWrap = document.getElementById('modalAssetImageWrap');
                const imgEl = document.getElementById('modalAssetImage');
                if (req?.asset?.asset_image) {
                    imgEl.src = req.asset.asset_image;
                    imgWrap.style.display = 'block';
                } else {
                    imgWrap.style.display = 'none';
                    imgEl.src = '';
                }
            }

            // Set dynamic schedule and location data
            document.getElementById('modalLocation').textContent = location || '—';
            document.getElementById('modalSchedule').textContent = startDate + ' to ' + endDate;
            document.getElementById('modalDuration').textContent = duration || '—';

            document.getElementById('reviewModal').classList.add('active');
        }

        function closeReviewModal() {
            document.getElementById('reviewModal').classList.remove('active');
        }

        function showModalError(msg) {
            const el = document.getElementById('modalError');
            const msgEl = document.getElementById('modalErrorMsg');
            if (el && msgEl) {
                msgEl.textContent = msg || 'An error occurred. Please try again.';
                el.classList.add('visible');
                setTimeout(() => el.classList.remove('visible'), 6000);
            }
        }

        function processAction(btn, decision) {
            const action = decision === 'Approved' ? 'confirm' : 'reject';
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="ph ph-spinner-gap"></i> Processing...';
            document.getElementById('modalError')?.classList.remove('visible');

            const btns = document.querySelectorAll('.modal-actions button');
            btns.forEach(b => b.disabled = true);
            window.api.authenticatedFetch('/transactions/lender_confirm.php', {
                method: 'PUT',
                body: {
                    transaction_id: document.getElementById('hiddenTxnId').value,
                    action
                }
            }).then(async (res) => {
                if (res && res.status === 'success') {
                    btn.innerHTML = decision === 'Approved' ? '<i class="ph ph-check"></i> Approved' : '<i class="ph ph-check"></i> Denied';
                    await loadPendingRequests();
                    setTimeout(() => {
                        closeReviewModal();
                        btn.innerHTML = originalText;
                        btns.forEach(b => b.disabled = false);
                    }, 600);
                } else {
                    showModalError(res?.message || 'Failed to process request.');
                    btn.innerHTML = originalText;
                    btns.forEach(b => b.disabled = false);
                }
            }).catch((e) => {
                showModalError(e?.message || 'Network error. Please try again.');
                btn.innerHTML = originalText;
                btns.forEach(b => b.disabled = false);
            });
        }
    </script>
  <script src="../js/theme.js"></script>

</body>

</html>
