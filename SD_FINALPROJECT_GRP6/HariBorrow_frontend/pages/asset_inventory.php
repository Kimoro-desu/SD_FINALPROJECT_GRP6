<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HariBorrow — Asset Inventory</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="../js/api.js"></script>
    <script src="../js/auth_guard.js"></script>
    <style>
        /* ── BASE STYLES ── */
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
            --info: #60a5fa;
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

        /* ── BACKGROUND EFFECTS ── */
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

        /* ── SIDEBAR ── */
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
        }

        .search-wrap {
            position: relative;
            flex: 1;
            max-width: 360px;
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

        /* ── BUTTONS ── */
        .btn-primary {
            background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            color: var(--bg-deep);
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            box-shadow: 0 4px 15px var(--gold-glow);
            transform: translateY(-1px);
        }

        .btn-icon {
            background: transparent;
            border: 1px solid var(--glass-border);
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text-2);
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-icon:hover {
            border-color: var(--gold);
            color: var(--gold);
            background: var(--gold-dim);
        }

        .btn-icon.danger:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: rgba(255, 107, 122, 0.1);
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

        .asset-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .asset-thumb {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .asset-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .asset-thumb i {
            font-size: 20px;
            color: var(--text-3);
        }

        /* ── STATUS PILLS ── */
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

        .status-available {
            background: rgba(74, 222, 128, 0.1);
            color: var(--success);
            border: 1px solid rgba(74, 222, 128, 0.2);
        }

        .status-available::before {
            background: var(--success);
        }

        .status-loan {
            background: rgba(96, 165, 250, 0.1);
            color: var(--info);
            border: 1px solid rgba(96, 165, 250, 0.2);
        }

        .status-loan::before {
            background: var(--info);
        }

        .status-maintenance {
            background: rgba(251, 191, 36, 0.1);
            color: var(--warning);
            border: 1px solid rgba(251, 191, 36, 0.2);
        }

        .status-maintenance::before {
            background: var(--warning);
        }

        /* ── MODALS ── */
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
            max-width: 600px;
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

        /* ── FORMS ── */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
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

        .form-input[readonly] {
            background: rgba(0, 0, 0, 0.2);
            border-color: transparent;
            color: var(--text-2);
            cursor: not-allowed;
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

        .lender-info-box {
            background: rgba(96, 165, 250, 0.05);
            border: 1px solid rgba(96, 165, 250, 0.15);
            border-radius: 10px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .lender-info-box i {
            font-size: 24px;
            color: var(--info);
        }

        .lender-info-text span {
            display: block;
            font-size: 11px;
            color: var(--text-3);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .lender-info-text strong {
            display: block;
            font-size: 14px;
            color: var(--text-1);
            font-weight: 500;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 12px;
            border-top: 1px solid var(--glass-border);
            padding-top: 24px;
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid var(--glass-border);
            padding: 12px 20px;
            border-radius: 10px;
            color: var(--text-2);
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-1);
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
            <a href="admin_dashboard.php" class="nav-link"><i class="ph ph-squares-four"></i> Command Center</a>
            <div class="nav-section-title">Operations</div>
            <a href="pendingrequest_approval.php" class="nav-link"><i class="ph ph-bell-ringing"></i> Pending Requests</a>
            <a href="active_transactions.php" class="nav-link"><i class="ph ph-clock-counter-clockwise"></i> Active
                Transactions</a>

            <div class="nav-section-title">Database</div>
            <a href="asset_inventory.php" class="nav-link active"><i class="ph ph-stack"></i> Asset Inventory</a>
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
                <a href="javascript:void(0);"
                    onclick="window.api ? window.api.removeToken() : localStorage.removeItem('jwt'); window.location.href='login.php'"
                    style="margin-left: auto; color: var(--text-3); cursor: pointer;"><i class="ph ph-sign-out"
                        style="font-size: 20px;"></i></a>
            </div>
        </div>
    </aside>

    <main class="main-content">

        <div class="header-area">
            <div class="page-title">
                <h1>Asset Inventory</h1>
                <p>Review, verify, and modify university equipment uploaded by department lenders.</p>
            </div>
        </div>

        <div class="toolbar">
            <div class="filters">
                <div class="search-wrap">
                    <i class="ph ph-magnifying-glass"></i>
                    <input class="search-input" type="text" id="assetSearch" name="search"
                        placeholder="Search by Asset ID, Name, or Lender...">
                </div>

                <select class="filter-select" id="categoryFilter" name="category_filter">
                    <option value="">All Categories</option>
                    <option value="electronics">Electronics</option>
                    <option value="computing">Computing</option>
                    <option value="mechanical">Mechanical</option>
                    <option value="furniture">Furniture</option>
                </select>

                <select class="filter-select" id="statusFilter" name="status_filter">
                    <option value="">All Statuses</option>
                    <option value="available">Available</option>
                    <option value="borrowed">Borrowed</option>
                    <option value="pending">Pending</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
        </div>

        <div class="data-panel">
            <table>
                <thead>
                    <tr>
                        <th>Asset Details</th>
                        <th>Asset Tag / ID</th>
                        <th>Uploaded By</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="assetInventoryBody"></tbody>
            </table>
        </div>
    </main>

    <div class="modal-overlay" id="assetModal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">
                    <i class="ph ph-clipboard-text" style="color: var(--gold);"></i>
                    <span>Review & Modify Asset</span>
                </div>
                <button class="modal-close" onclick="closeAssetModal()"><i class="ph ph-x"></i></button>
            </div>

            <form id="updateAssetForm" onsubmit="submitAssetUpdate(event)">

                <input type="hidden" name="asset_id_hidden" id="formAssetIdHidden" value="">

                <div class="lender-info-box">
                    <i class="ph ph-buildings"></i>
                    <div class="lender-info-text">
                        <span>Uploaded By (Lender)</span>
                        <strong id="modalLenderName">[Lender Dept/Name]</strong>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Asset Name</label>
                        <input type="text" class="form-input" name="asset_name" id="formAssetName" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Asset Tag / Serial</label>
                        <input type="text" class="form-input" name="asset_tag" id="formAssetTag" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="asset_category" id="formCategory" required>
                            <option value="">Select Category...</option>
                            <option value="electronics">Electronics</option>
                            <option value="computing">Computing</option>
                            <option value="mechanical">Mechanical</option>
                            <option value="furniture">Furniture</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Current Status</label>
                        <select class="form-select" name="asset_status" id="formStatus" required>
                            <option value="Available">Available</option>
                            <option value="Borrowed">Borrowed</option>
                            <option value="Pending">Pending</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Designated Location</label>
                    <input type="text" class="form-input" name="asset_location" id="formLocation">
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeAssetModal()">Cancel</button>
                    <button type="submit" class="btn-primary"><i class="ph ph-check"></i> Save Modifications</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="deleteModal">
        <div class="modal" style="max-width: 400px; text-align: center; padding-top: 48px;">
            <div
                style="width: 64px; height: 64px; border-radius: 50%; background: rgba(255, 107, 122, 0.1); border: 1px solid rgba(255, 107, 122, 0.3); color: var(--danger); font-size: 32px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="ph ph-warning"></i>
            </div>

            <h2
                style="font-family: 'Cormorant Garamond', serif; font-size: 28px; font-weight: 500; margin-bottom: 12px; color: var(--text-1);">
                Delete Asset?</h2>

            <p style="font-size: 13px; color: var(--text-2); line-height: 1.5; margin-bottom: 32px;">
                Are you sure you want to permanently delete <strong id="deleteAssetName"
                    style="color: var(--text-1);">[Asset]</strong> (<span id="deleteAssetTag"
                    style="font-family: monospace; color: var(--gold);">[Tag]</span>)? This action cannot be undone.
            </p>

            <form id="deleteAssetForm" onsubmit="submitAssetDelete(event)">
                <input type="hidden" name="asset_id_to_delete" id="deleteHiddenId" value="">

                <div style="display: flex; gap: 12px;">
                    <button type="button" class="btn-secondary" style="flex: 1;"
                        onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" class="btn-primary"
                        style="flex: 1; background: var(--danger); color: #fff; justify-content: center;">Yes,
                        Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        /* GLOW EFFECT */
        const glow = document.getElementById('glow');
        document.addEventListener('mousemove', (e) => {
            glow.style.setProperty('--mouse-x', e.clientX + 'px');
            glow.style.setProperty('--mouse-y', e.clientY + 'px');
        });

        /* ASSET REVIEW/EDIT MODAL */
        function openAssetModal(id, name, category, status, lenderName, description) {
            const modal = document.getElementById('assetModal');

            // Populate fields for reviewing/editing
            document.getElementById('formAssetIdHidden').value = id;
            document.getElementById('formAssetTag').value = `#AST-${id}`;
            document.getElementById('formAssetName').value = name;
            document.getElementById('formLocation').value = description || '';
            document.getElementById('modalLenderName').textContent = lenderName;

            // Ensure current category exists in select options
            const categorySelect = document.getElementById('formCategory');
            if (category && !Array.from(categorySelect.options).some(o => o.value === category)) {
                const opt = document.createElement('option');
                opt.value = category;
                opt.textContent = category;
                categorySelect.appendChild(opt);
            }
            categorySelect.value = category || '';

            document.getElementById('formStatus').value = status;

            modal.classList.add('active');
        }

        function closeAssetModal() {
            document.getElementById('assetModal').classList.remove('active');
        }

        /* DELETE MODAL */
        function openDeleteModal(id, name) {
            document.getElementById('deleteAssetTag').textContent = `#AST-${id}`;
            document.getElementById('deleteAssetName').textContent = name;
            document.getElementById('deleteHiddenId').value = id;
            document.getElementById('deleteModal').classList.add('active');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }
    </script>

    <script>
        let allAssets = [];

        function esc(v) {
            return String(v ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        function statusClass(status) {
            const s = String(status || '').toLowerCase();
            if (s.includes('avail')) return 'status-available';
            if (s.includes('maint')) return 'status-maintenance';
            return 'status-loan';
        }

        function iconForType(type) {
            const t = String(type || '').toLowerCase();
            if (t.includes('camera')) return 'ph-camera';
            if (t.includes('projector')) return 'ph-projector-screen';
            if (t.includes('audio')) return 'ph-speaker-high';
            if (t.includes('lab')) return 'ph-flask';
            return 'ph-cpu';
        }

        function fmtDate(val) {
            if (!val) return '—';
            const d = new Date(val);
            if (Number.isNaN(d.getTime())) return String(val);
            return d.toLocaleDateString();
        }

        function renderAssets() {
            const tbody = document.getElementById('assetInventoryBody');
            if (!tbody) return;
            const q = (document.getElementById('assetSearch')?.value || '').trim().toLowerCase();
            const category = (document.getElementById('categoryFilter')?.value || '').trim().toLowerCase();
            const status = (document.getElementById('statusFilter')?.value || '').trim().toLowerCase();

            const filtered = allAssets.filter(a => {
                const searchBlob = `${a.id} ${a.name} ${a.type} ${a.lender_name}`.toLowerCase();
                const aType = String(a.type || '').toLowerCase();
                const aStatus = String(a.status || '').toLowerCase();
                return (!q || searchBlob.includes(q)) &&
                    (!category || aType.includes(category)) &&
                    (!status || aStatus.includes(status));
            });

            tbody.innerHTML = filtered.map(a => `
                <tr>
                    <td>
                        <div class="asset-cell">
                            <div class="asset-thumb"><i class="ph ${iconForType(a.type)}"></i></div>
                            <div>
                                <strong style="color: var(--text-1); font-weight: 500; display: block; margin-bottom: 2px;">${esc(a.name)}</strong>
                                <span style="font-size: 11px; color: var(--text-3);">Added: ${esc(fmtDate(a.created_at))}</span>
                            </div>
                        </div>
                    </td>
                    <td style="font-family: monospace; color: var(--gold);">#AST-${esc(a.id)}</td>
                    <td>${esc(a.lender_name || '—')}</td>
                    <td>${esc(a.type || '—')}</td>
                    <td><span class="status-pill ${statusClass(a.status)}">${esc(a.status || '—')}</span></td>
                    <td style="text-align: right; white-space: nowrap;">
                        <button class="btn-icon" title="Review & Edit"
                            onclick='openAssetModal(${JSON.stringify(a.id)}, ${JSON.stringify(a.name || '')}, ${JSON.stringify(a.type || '')}, ${JSON.stringify(a.status || '')}, ${JSON.stringify(a.lender_name || '')}, ${JSON.stringify(a.description || '')})'><i class="ph ph-magnifying-glass-plus"></i></button>
                        <button class="btn-icon danger" title="Delete Asset"
                            onclick='openDeleteModal(${JSON.stringify(a.id)}, ${JSON.stringify(a.name || '')})'><i class="ph ph-trash"></i></button>
                    </td>
                </tr>
            `).join('') || `
                <tr>
                    <td colspan="6" style="padding:18px; color: var(--text-3);">No assets found.</td>
                </tr>
            `;
        }

        async function loadAssets() {
            try {
                const res = await window.api.rawFetch('/api/assets/list_assets.php');
                allAssets = Array.isArray(res?.assets) ? res.assets : [];
                renderAssets();
            } catch (e) {
                console.error('Failed to load assets:', e);
                const tbody = document.getElementById('assetInventoryBody');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="6" style="padding:18px; color: var(--danger);">Failed to load assets.</td></tr>';
                }
            }
        }

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
                const initial = firstName.charAt(0).toUpperCase();

                const avatar = document.getElementById('sidebarAvatar');
                if (avatar) avatar.textContent = initial;

                const nameSpan = document.getElementById('sidebarName');
                if (nameSpan) nameSpan.textContent = `${firstName} ${lastName}`.trim();

                const roleSpan = document.getElementById('sidebarRole');
                if (roleSpan) {
                    const rawRole = (user.role || '').trim().toLowerCase();
                    roleSpan.textContent = rawRole ? (rawRole.charAt(0).toUpperCase() + rawRole.slice(1)) : 'Admin';
                }
            }

            await loadAssets();

            document.getElementById('assetSearch')?.addEventListener('input', renderAssets);
            document.getElementById('categoryFilter')?.addEventListener('change', renderAssets);
            document.getElementById('statusFilter')?.addEventListener('change', renderAssets);
        });

        async function submitAssetUpdate(event) {
            event.preventDefault();
            const assetId = document.getElementById('formAssetIdHidden').value;
            if (!assetId) return;
            try {
                await window.api.authenticatedFetch(`/api/assets/update.php?id=${encodeURIComponent(assetId)}`, {
                    method: 'PUT',
                    body: {
                        asset_name: document.getElementById('formAssetName').value,
                        asset_type: document.getElementById('formCategory').value,
                        description: document.getElementById('formLocation').value,
                        availability: document.getElementById('formStatus').value
                    }
                });
                closeAssetModal();
                await loadAssets();
            } catch (e) {
                alert(e?.message || 'Failed to update asset.');
            }
        }

        async function submitAssetDelete(event) {
            event.preventDefault();
            const assetId = document.getElementById('deleteHiddenId').value;
            if (!assetId) return;
            try {
                await window.api.authenticatedFetch(`/api/assets/delete.php?id=${encodeURIComponent(assetId)}`, {
                    method: 'DELETE'
                });
                closeDeleteModal();
                await loadAssets();
            } catch (e) {
                alert(e?.message || 'Failed to delete asset.');
            }
        }
    </script>
</body>

</html>