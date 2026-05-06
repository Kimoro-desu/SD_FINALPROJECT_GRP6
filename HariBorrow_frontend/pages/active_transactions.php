<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HariBorrow — Transactions</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="../js/api.js"></script>
    <script src="../js/auth_guard.js?v=1778041298"></script>
    <style>
        /* * BASE STYLES */
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
            --info: #60a5fa;
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
            background: radial-gradient(480px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(229, 192, 123, 0.06), transparent 50%);
            z-index: 9999;
            mix-blend-mode: screen;
            transition: background 0.08s ease-out;
        }

        /* SIDEBAR */
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

        /* MAIN CONTENT & TOOLBAR */
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

        /* DATA TABLE */
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
            padding: 12px 16px; /* Reduced padding */
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--text-3);
            border-bottom: 1px solid var(--glass-border);
            background: rgba(0, 0, 0, 0.2);
            white-space: nowrap; /* Prevents headers from wrapping weirdly */
        }

        td {
            padding: 12px 16px; /* Reduced padding */
            font-size: 13px; /* Smaller font to fit data */
            color: var(--text-2);
            font-weight: 300;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        /* STATUS PILLS */
        .status-pill {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-pill.active {
            background: rgba(96, 165, 250, 0.1);
            color: var(--info);
            border: 1px solid rgba(96, 165, 250, 0.3);
        }

        .status-pill.returned {
            background: rgba(74, 222, 128, 0.1);
            color: var(--success);
            border: 1px solid rgba(74, 222, 128, 0.3);
        }

        .status-pill.overdue {
            background: rgba(255, 107, 122, 0.1);
            color: var(--danger);
            border: 1px solid rgba(255, 107, 122, 0.3);
        }

        .status-pill.return-pending {
            background: rgba(229, 192, 123, 0.15);
            color: var(--gold);
            border: 1px solid rgba(229, 192, 123, 0.3);
            animation: pulse-gold 2s ease-in-out infinite;
        }

        @keyframes pulse-gold {
            0%, 100% { box-shadow: 0 0 0 0 rgba(229, 192, 123, 0); }
            50% { box-shadow: 0 0 0 4px rgba(229, 192, 123, 0.15); }
        }

        /* MODAL */
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
            padding: 40px;
            width: 100%;
            max-width: 500px;
            transform: scale(0.95);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            max-height: 90vh;
            overflow-y: auto;
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
            background: var(--red-dim);
            border-color: rgba(248, 113, 113, 0.3);
            color: var(--danger);
        }

        /* FORM ELEMENTS FOR MODAL */
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

        .form-input,
        .form-textarea,
        .form-select {
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

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            border-color: var(--gold);
        }

        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-select {
            appearance: none;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23E5C07B' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
        }

        .form-select option {
            background: var(--bg-deep);
            color: var(--text-1);
        }

        /* NEW: PROOF VIEWER STYLING */
        .proof-viewer {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .proof-thumb-wrap {
            width: 64px;
            height: 64px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .proof-thumb-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .proof-thumb-wrap i {
            font-size: 24px;
            color: var(--text-3);
            position: absolute;
        }

        .proof-info {
            flex: 1;
        }

        .proof-filename {
            font-size: 13px;
            color: var(--text-1);
            margin-bottom: 4px;
            display: block;
            word-break: break-all;
        }

        .proof-timestamp {
            font-size: 11px;
            color: var(--text-3);
        }

        .btn-view-proof {
            background: transparent;
            border: 1px solid var(--gold);
            color: var(--gold);
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-view-proof:hover {
            background: var(--gold-dim);
        }

        .summary-box {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
        }

        .summary-row span {
            color: var(--text-3);
        }

        .summary-row strong {
            color: var(--text-1);
            font-weight: 400;
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
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .btn-submit:hover {
            box-shadow: 0 4px 15px var(--gold-glow);
            transform: translateY(-2px);
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
      <a href="active_transactions.php" class="nav-link active"><i class="ph ph-clock-counter-clockwise"></i> All Transactions</a>
            <div class="nav-section-title">Database</div>       
            <a href="asset_inventory.php" class="nav-link"><i class="ph ph-stack"></i> Asset Inventory</a>
            <a href="registered_users.php" class="nav-link"><i class="ph ph-users"></i> Registered Users</a>

            <div class="nav-section-title">System</div>
            <a href="registration_approval.php" class="nav-link"><i class="ph ph-shield-check"></i> Registration Approvals
                <span class="nav-badge" id="regBadge" style="display:none;">0</span>
            </a>
            <a href="system_logs.php" class="nav-link"><i class="ph ph-file-text"></i> System Logs</a>
        </nav>

        <div class="sidebar-footer">
            <div class="admin-profile">
                <div class="admin-avatar" id="sidebarAvatar">UN</div>
                <div class="admin-info">
                    <span class="admin-name" id="sidebarName">Admin Name</span>
                    <span class="admin-role" id="sidebarRole">Admin</span>
                </div>
                <a href="javascript:void(0);" onclick="window.api ? window.api.removeToken() : localStorage.removeItem('jwt'); window.location.href='login.php'" style="margin-left: auto; color: var(--text-3); cursor: pointer;"><i
                        class="ph ph-sign-out" style="font-size: 20px;"></i></a>
            </div>
        </div>
    </aside>

    <main class="main-content">

        <div class="header-area">
            <div class="page-title">
                <h1>Transactions</h1>
                <p>Monitor all system transactions, historical logs, and process incoming returns.</p>
            </div>
        </div>

        <div class="toolbar">
            <div class="search-wrap">
                <i class="ph ph-magnifying-glass"></i>
                <input class="search-input" type="text" id="activeSearch" name="search_query"
                    placeholder="Search by TXN ID, Asset, or Borrower...">
            </div>

            <select class="filter-select" id="activeStatusFilter" name="status_filter">
                <option value="">All Statuses</option>
                <option value="active">On Loan (Active)</option>
                <option value="returned">Returned</option>
                <option value="overdue">Overdue</option>
            </select>
        </div>

        <div class="data-panel">
            <div style="width: 100%; overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Borrower Details</th>
                            <th>Asset Information</th>
                            <th>Borrow Date</th>
                            <th>Expected Return</th>
                            <th>Time Returned</th>
                            <th>Penalty</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="activeTransactionsBody"></tbody>
                </table>
            </div>
        </div>

    </main>

    <div class="modal-overlay" id="returnModal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title" style="color: var(--text-1);">
                    <i class="ph ph-file-text" style="color: var(--gold);"></i>
                    Transaction Details
                </div>
                <button class="modal-close" onclick="closeReturnModal()"><i class="ph ph-x"></i></button>
            </div>

            <form method="POST" action="process_return.php" id="returnForm">

                <input type="hidden" name="transaction_id" id="hiddenTxnId" value="">

                <div class="summary-box">
                    <div class="summary-row"><span>Transaction:</span><strong id="modalTxnId"
                            style="font-family: monospace; color: var(--gold);"></strong></div>
                    <div class="summary-row"><span>Borrower:</span><strong id="modalBorrower"></strong></div>
                    <div class="summary-row"><span>Asset:</span><strong id="modalAsset"></strong></div>
                    <div class="summary-row"><span>Borrow Date:</span><strong id="modalBorrowDate"></strong></div>
                    <div class="summary-row"><span>Due Date:</span><strong id="modalDueDate"></strong></div>
                    <div class="summary-row"><span>Actual Return:</span><strong id="modalReturnedAt">—</strong></div>
                    <div class="summary-row"><span>Current Penalty:</span><strong id="modalPenalty"></strong></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Borrower's Submitted Proof</label>
                    <div id="proofContainer">
                        <div class="proof-viewer" id="noProofMsg">
                            <div class="proof-thumb-wrap">
                                <i class="ph ph-image"></i>
                            </div>
                            <div class="proof-info">
                                <span class="proof-filename">No photos uploaded</span>
                                <span class="proof-timestamp">Borrower did not submit return photos</span>
                            </div>
                        </div>
                        <div id="proofGallery" style="display:none; display:flex; flex-wrap:wrap; gap:10px;"></div>
                    </div>
                </div>

                <div id="adminAssessmentBlock" style="display:none;">
                    <div class="form-group">
                        <label class="form-label">Admin Assessment: Asset Condition</label>
                        <select class="form-select" name="return_condition" required>
                            <option value="good">Good Condition / Matches Proof</option>
                            <option value="minor_damage">Minor Damage / Wear & Tear</option>
                            <option value="major_damage">Major Damage / Needs Repair</option>
                            <option value="lost">Asset Declared Lost</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Admin Remarks (Optional)</label>
                        <textarea class="form-textarea" name="admin_remarks"
                            placeholder="Note any missing accessories, specific damage details, or reasons for late return penalties..."></textarea>
                    </div>

                    <button type="button" class="btn-submit" id="approveReturnBtn" onclick="approveReturn(this)" style="background: var(--success); margin-top:12px;">
                        <i class="ph ph-check-circle"></i> Approve Return
                    </button>
                    <button type="button" class="btn-submit" id="rejectReturnBtn" onclick="rejectReturn(this)" style="background: var(--danger); margin-top:8px;">
                        <i class="ph ph-x-circle"></i> Reject Return
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script>
        /* MOUSE GLOW EFFECT */
        const glow = document.getElementById('glow');
        document.addEventListener('mousemove', (e) => {
            glow.style.setProperty('--mouse-x', e.clientX + 'px');
            glow.style.setProperty('--mouse-y', e.clientY + 'px');
        });

        let allActiveTransactions = [];

        function esc(v) {
            return String(v ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        /** DB stores Philippines local wall time; parse without interpreting as UTC. */
        function parseMysqlDateTimeLocal(s) {
            if (!s || typeof s !== 'string') return null;
            const m = String(s).trim().match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?/);
            if (!m) return null;
            return new Date(+m[1], +m[2] - 1, +m[3], +m[4], +m[5], +(m[6] || 0));
        }

        function isOverdue(dueDate) {
            if (!dueDate) return false;
            const d = parseMysqlDateTimeLocal(String(dueDate)) || new Date(dueDate);
            return !Number.isNaN(d.getTime()) && d.getTime() < Date.now();
        }

        function fmtDateTime(val) {
            if (!val) return '—';
            const d = parseMysqlDateTimeLocal(String(val)) || new Date(val);
            if (Number.isNaN(d.getTime())) return String(val);
            return d.toLocaleString();
        }

        function tsFromDateVal(val) {
            if (!val) return NaN;
            const d = parseMysqlDateTimeLocal(String(val)) || new Date(val);
            return d.getTime();
        }

        function renderActiveTransactions() {
            const tbody = document.getElementById('activeTransactionsBody');
            if (!tbody) return;

            const q = (document.getElementById('activeSearch')?.value || '').trim().toLowerCase();
            const statusFilter = (document.getElementById('activeStatusFilter')?.value || '').trim().toLowerCase();

            const filtered = allActiveTransactions.filter(tx => {
                const st = String(tx?.status || '').toLowerCase();
                const hasReturnedDate = !!tx?.dates?.returned;
                const isReturnPending = st === 'return_pending';
                const overdue = !isReturnPending && !hasReturnedDate && st === 'approved' && isOverdue(tx?.dates?.due);

                let statusKey = st; 
                if (hasReturnedDate) statusKey = 'returned';
                else if (isReturnPending) statusKey = 'return_pending';
                else if (overdue) statusKey = 'overdue';
                else if (st === 'approved') statusKey = 'active';

                const blob = `${tx?.transaction_id} ${tx?.borrower?.name} ${tx?.borrower?.school_id} ${tx?.asset?.name}`.toLowerCase();
                return (!q || blob.includes(q)) && (!statusFilter || statusFilter === statusKey);
            });

            tbody.innerHTML = filtered.map(tx => {
                const st = String(tx?.status || '').toLowerCase();
                const hasReturnedDate = !!tx?.dates?.returned;
                const isReturnPending = st === 'return_pending';
                const overdue = !isReturnPending && !hasReturnedDate && st === 'approved' && isOverdue(tx?.dates?.due);
                
                const due = fmtDateTime(tx?.dates?.due);
                const borrowed = fmtDateTime(tx?.dates?.borrowed || tx?.dates?.requested);
                const returned = fmtDateTime(tx?.dates?.returned);
                let returnCompareTitle = '';
                if (tx?.dates?.returned && tx?.dates?.due) {
                    const rts = tsFromDateVal(tx.dates.returned);
                    const dts = tsFromDateVal(tx.dates.due);
                    if (!Number.isNaN(rts) && !Number.isNaN(dts)) {
                        returnCompareTitle = rts <= dts ? 'Returned on or before the expected return time' : 'Returned after the expected return time (late)';
                    }
                }
                const txNum = tx?.transaction_id ?? '';
                
                const dailyPenalty = Number(tx?.asset?.daily_penalty || 0);
                let penalty = Number(tx?.penalty_amount || 0);
                if (overdue && dailyPenalty > 0) {
                        const dueTs = tsFromDateVal(tx?.dates?.due);
                        if (!Number.isNaN(dueTs)) {
                        const lateDays = Math.max(1, Math.ceil((Date.now() - dueTs) / 86400000));
                        penalty = lateDays * dailyPenalty;
                    }
                }
                
                const returnPhotos = JSON.stringify(tx?.return_photos || []);
                
                let statusPill, statusLabel;
                if (hasReturnedDate) {
                    statusPill = 'returned';
                    statusLabel = 'Returned';
                } else if (isReturnPending) {
                    statusPill = 'return-pending';
                    statusLabel = 'Return Pending';
                } else if (st === 'return_lender_confirm') {
                    statusPill = 'return-pending';
                    statusLabel = 'Lender Confirming';
                } else if (overdue) {
                    statusPill = 'overdue';
                    statusLabel = 'Overdue';
                } else if (st === 'pending') {
                    statusPill = 'return-pending'; 
                    statusLabel = 'Pending';
                } else if (st === 'rejected') {
                    statusPill = 'overdue';
                    statusLabel = 'Rejected';
                } else if (st === 'approved') {
                    statusPill = 'active';
                    statusLabel = 'On Loan';
                } else {
                    statusPill = 'active';
                    statusLabel = st;
                }

                const actionLabel = isReturnPending ? 'Review Return' : 'View Details';
                const icon = isReturnPending ? 'ph-arrow-u-down-left' : 'ph-eye';
                
                return `
                    <tr>
                        <td style="color: var(--text-1); font-family: monospace;">#TXN-${esc(txNum)}</td>
                        <td>${esc(tx?.borrower?.name || '—')} <br><span style="font-size: 11px; color: var(--text-3);">${esc(tx?.borrower?.school_id || '—')}</span></td>
                        <td style="color: var(--text-1);">${esc(tx?.asset?.name || '—')}<br><span style="font-size: 11px; color: var(--text-3);">Asset ID: ${esc(tx?.asset?.id || '—')}</span></td>
                        <td style="white-space: nowrap;">${esc(borrowed)}</td>
                        <td style="white-space: nowrap;"><span style="color: ${overdue ? 'var(--danger)' : 'var(--text-1)'};">${esc(due)}</span></td>
                        <td style="white-space: nowrap; color: var(--text-2);" title="${esc(returnCompareTitle)}">${esc(returned)}</td>
                        <td style="white-space: nowrap; color: ${penalty > 0 ? 'var(--danger)' : 'var(--text-2)'};">PHP ${penalty.toFixed(2)}</td>
                        <td><span class="status-pill ${statusPill}">${statusLabel}</span></td>
                        <td>
                            <button class="btn-outline btn-primary"
                                onclick='openReturnModal(${JSON.stringify(txNum)}, ${JSON.stringify(tx?.borrower?.name || '')}, ${JSON.stringify(tx?.asset?.name || '')}, ${JSON.stringify(borrowed)}, ${JSON.stringify(due)}, ${JSON.stringify(returned)}, ${JSON.stringify(`PHP ${penalty.toFixed(2)}`)}, ${returnPhotos.replace(/'/g, "&#39;")}, ${JSON.stringify(isReturnPending)})'>
                                <i class="ph ${icon}"></i> ${actionLabel}
                            </button>
                        </td>
                    </tr>
                `;
            }).join('') || `<tr><td colspan="9" style="padding: 18px 20px; color: var(--text-3);">No transactions found matching your criteria.</td></tr>`;
        }

        async function loadActiveTransactions() {
            try {
                const res = await window.api.authenticatedFetch('/api/transactions/history.php');
                allActiveTransactions = Array.isArray(res?.history) ? res.history : [];
                renderActiveTransactions();
            } catch (e) {
                console.error('Failed loading transactions:', e);
                const tbody = document.getElementById('activeTransactionsBody');
                if (tbody) tbody.innerHTML = '<tr><td colspan="9" style="padding:18px; color: var(--danger);">Failed to load transactions.</td></tr>';
            }
        }

        function openReturnModal(txnId, borrower, asset, borrowDate, dueDate, actualReturn, penalty, returnPhotos, isReturnPending) {
            document.getElementById('hiddenTxnId').value = txnId;
            document.getElementById('modalTxnId').textContent = `#TXN-${txnId}`;
            document.getElementById('modalBorrower').textContent = borrower;
            document.getElementById('modalAsset').textContent = asset;
            document.getElementById('modalBorrowDate').textContent = borrowDate;
            document.getElementById('modalDueDate').textContent = dueDate;
            const raEl = document.getElementById('modalReturnedAt');
            if (raEl) raEl.textContent = actualReturn || '—';
            document.getElementById('modalPenalty').textContent = penalty;

            // Display return photos
            const gallery = document.getElementById('proofGallery');
            const noProof = document.getElementById('noProofMsg');
            const photos = Array.isArray(returnPhotos) ? returnPhotos : [];

            if (photos.length > 0) {
                noProof.style.display = 'none';
                gallery.style.display = 'flex';
                gallery.innerHTML = photos.map(p => `
                    <div class="proof-thumb-wrap" style="width:80px;height:80px;cursor:pointer;" onclick="window.open('${esc(p.photo_path)}','_blank')">
                        <img src="${esc(p.photo_path)}" alt="Return photo" style="display:block;">
                    </div>
                `).join('');
            } else {
                noProof.style.display = 'flex';
                gallery.style.display = 'none';
                gallery.innerHTML = '';
            }

            // Show/hide approve/reject buttons and form based on status
            const assessmentBlock = document.getElementById('adminAssessmentBlock');
            if (isReturnPending) {
                assessmentBlock.style.display = 'block';
            } else {
                assessmentBlock.style.display = 'none';
            }

            document.getElementById('returnModal').classList.add('active');
        }

        function closeReturnModal() {
            document.getElementById('returnModal').classList.remove('active');
            document.getElementById('returnForm').reset();
        }

        async function approveReturn(btn) {
            const txnId = document.getElementById('hiddenTxnId').value;
            if (!txnId) return;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="ph ph-spinner-gap"></i> Approving...';
            btn.style.pointerEvents = 'none';

            try {
                const res = await window.api.authenticatedFetch('/api/transactions/approve_return.php', {
                    method: 'PUT',
                    body: { transaction_id: txnId, action: 'approve' }
                });
                btn.innerHTML = '<i class="ph ph-check"></i> Approved!';
                const p = Number(res?.penalty_amount || 0);
                if (p > 0) {
                    alert(`Return approved. Penalty due: PHP ${p.toFixed(2)}`);
                } else {
                    alert('Return approved successfully. Asset released back to catalog.');
                }
                await loadActiveTransactions();
                setTimeout(() => {
                    closeReturnModal();
                    btn.innerHTML = originalText;
                    btn.style.pointerEvents = 'auto';
                }, 600);
            } catch (err) {
                alert(err?.message || 'Failed to approve return.');
                btn.innerHTML = originalText;
                btn.style.pointerEvents = 'auto';
            }
        }

        async function rejectReturn(btn) {
            const txnId = document.getElementById('hiddenTxnId').value;
            if (!txnId) return;
            if (!confirm('Are you sure you want to reject this return? The transaction will revert to active.')) return;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="ph ph-spinner-gap"></i> Rejecting...';
            btn.style.pointerEvents = 'none';

            try {
                await window.api.authenticatedFetch('/api/transactions/approve_return.php', {
                    method: 'PUT',
                    body: { transaction_id: txnId, action: 'reject' }
                });
                alert('Return rejected. Transaction reverted to active loan.');
                await loadActiveTransactions();
                setTimeout(() => {
                    closeReturnModal();
                    btn.innerHTML = originalText;
                    btn.style.pointerEvents = 'auto';
                }, 600);
            } catch (err) {
                alert(err?.message || 'Failed to reject return.');
                btn.innerHTML = originalText;
                btn.style.pointerEvents = 'auto';
            }
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            let user = window.api ? window.api.getUser() : null;
            if (!user || !user.name) {
                try {
                    const data = await window.api.authenticatedFetch('/users/profile.php');
                    if (data && data.status === 'success') {
                        user = {
                            id: data.profile.id,
                            name: data.profile.full_name,
                            email: data.profile.email,
                            role: data.profile.role
                        };
                        window.api.setUser(user);
                    }
                } catch (e) { console.error('Failed to fetch profile', e); }
            }

            if (user && user.name) {
                const firstName = user.name.split(' ')[0] || 'Admin';
                const lastName = user.name.split(' ').slice(1).join(' ') || '';
                const initial = (firstName + (lastName ? ' ' + lastName : '')).trim().split(/\s+/).length > 1 ? (firstName[0] + (lastName ? lastName[0] : firstName[0])).toUpperCase() : (firstName.length > 1 ? firstName.substring(0, 2) : firstName + firstName).toUpperCase();

                const avatar = document.getElementById('sidebarAvatar');
//                 if (avatar) avatar.textContent = initial;

                const nameSpan = document.getElementById('sidebarName');
                if (nameSpan) nameSpan.textContent = `${firstName} ${lastName}`.trim();

                const roleSpan = document.getElementById('sidebarRole');
                if (roleSpan) {
                    const rawRole = (user.role || '').trim().toLowerCase();
                    roleSpan.textContent = rawRole ? (rawRole.charAt(0).toUpperCase() + rawRole.slice(1)) : 'Admin';
                }
            }

            await loadActiveTransactions();
            document.getElementById('activeSearch')?.addEventListener('input', renderActiveTransactions);
            document.getElementById('activeStatusFilter')?.addEventListener('change', renderActiveTransactions);
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