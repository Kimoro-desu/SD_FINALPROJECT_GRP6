<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HariBorrow — Asset Management</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600&family=Pinyon+Script&display=swap"
    rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <script src="../js/auth_guard.js"></script>
  <script src="../js/api.js"></script>
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

    /* ── BACKGROUND ── */
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
      0% {
        transform: scale(1);
      }

      100% {
        transform: scale(1.05);
      }
    }

    .ambient-glow {
      position: fixed;
      inset: 0;
      pointer-events: none;
      background: radial-gradient(600px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(229, 192, 123, 0.06), transparent 50%);
      z-index: 9999;
      mix-blend-mode: screen;
    }

    /* ── TOP NAV ── */
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
      font-weight: 400;
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

    .drop-item:hover i {
      opacity: 1;
    }

    .drop-divider {
      height: 1px;
      background: var(--glass-border);
      margin: 6px 0;
    }

    .drop-item.logout {
      color: #ff6b7a;
    }

    .drop-item.logout i {
      color: #ff6b7a;
    }

    .drop-item.logout:hover {
      background: rgba(220, 53, 69, 0.1);
    }

    /* ── MAIN LAYOUT ── */
    .page {
      position: relative;
      z-index: 10;
      padding: 120px 5% 60px;
      max-width: 1400px;
      margin: 0 auto;
    }

    /* ── PAGE HEADER ── */
    .page-header {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      margin-bottom: 24px;
      padding-bottom: 0;
      flex-wrap: wrap;
      gap: 16px;
      animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .page-header-left h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 36px;
      font-weight: 500;
      color: var(--text-1);
      line-height: 1.1;
    }

    .page-header-left p {
      font-size: 13px;
      color: var(--text-2);
      font-weight: 300;
      margin-top: 6px;
    }

    .btn-primary {
      display: flex;
      align-items: center;
      gap: 8px;
      background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
      border: none;
      padding: 12px 24px;
      border-radius: 30px;
      color: var(--bg-deep);
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      font-weight: 600;
      letter-spacing: 0.05em;
      cursor: pointer;
      transition: all 0.3s;
      box-shadow: 0 4px 20px var(--gold-glow);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 30px var(--gold-glow);
    }

    /* ── PAGE NAV TABS ── */
    .page-nav {
      display: flex;
      gap: 32px;
      border-bottom: 1px solid var(--glass-border);
      margin-bottom: 32px;
      animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.05s both;
    }

    .page-nav-btn {
      background: transparent;
      border: none;
      padding: 0 0 16px 0;
      color: var(--text-3);
      font-family: 'Outfit', sans-serif;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      position: relative;
      transition: all 0.3s;
    }

    .page-nav-btn:hover {
      color: var(--text-2);
    }

    .page-nav-btn.active {
      color: var(--gold-light);
    }

    .page-nav-btn::after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 0;
      width: 100%;
      height: 2px;
      background: var(--gold);
      transform: scaleX(0);
      transition: transform 0.3s;
    }

    .page-nav-btn.active::after {
      transform: scaleX(1);
    }

    /* ── VIEWS ── */
    .view-section {
      display: none;
      animation: fadeUp 0.4s ease;
    }

    .view-section.active {
      display: block;
    }

    /* ── STATS BAR ── */
    .stats-bar {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
      margin-bottom: 28px;
    }

    .stat-card {
      background: var(--glass);
      backdrop-filter: blur(16px);
      border: 1px solid var(--glass-border);
      border-radius: 12px;
      padding: 20px 24px;
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .stat-icon {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      flex-shrink: 0;
    }

    .stat-icon.gold {
      background: var(--gold-dim);
      color: var(--gold);
    }

    .stat-icon.green {
      background: var(--green-dim);
      color: var(--green);
    }

    .stat-icon.red {
      background: var(--red-dim);
      color: var(--red);
    }

    .stat-info {
      flex: 1;
    }

    .stat-value {
      font-family: 'Cormorant Garamond', serif;
      font-size: 28px;
      font-weight: 600;
      color: var(--text-1);
      line-height: 1;
    }

    .stat-label {
      font-size: 11px;
      color: var(--text-3);
      font-weight: 400;
      margin-top: 4px;
      text-transform: uppercase;
      letter-spacing: 0.1em;
    }

    /* ── TOOLBAR ── */
    .toolbar {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 20px;
      flex-wrap: wrap;
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
      transition: border-color 0.2s;
    }

    .filter-select:focus {
      border-color: rgba(229, 192, 123, 0.4);
      color: var(--text-1);
    }

    .view-toggle {
      display: flex;
      border: 1px solid var(--glass-border);
      border-radius: 10px;
      overflow: hidden;
    }

    .view-btn {
      background: transparent;
      border: none;
      padding: 10px 14px;
      color: var(--text-3);
      cursor: pointer;
      transition: all 0.2s;
      font-size: 18px;
    }

    .view-btn.active {
      background: var(--gold-dim);
      color: var(--gold);
    }

    /* ── ASSET TABLE ── */
    .asset-table-wrap {
      background: var(--glass);
      backdrop-filter: blur(16px);
      border: 1px solid var(--glass-border);
      border-radius: 16px;
      overflow: hidden;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead tr {
      border-bottom: 1px solid var(--glass-border);
    }

    thead th {
      padding: 16px 20px;
      text-align: left;
      font-size: 10px;
      font-weight: 500;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--text-3);
      white-space: nowrap;
    }

    thead th.sortable {
      cursor: pointer;
      user-select: none;
    }

    thead th.sortable:hover {
      color: var(--gold);
    }

    thead th .sort-icon {
      font-size: 14px;
      vertical-align: middle;
      margin-left: 4px;
      opacity: 0.5;
    }

    tbody tr {
      border-bottom: 1px solid rgba(255, 255, 255, 0.03);
      transition: background 0.2s;
    }

    tbody tr:last-child {
      border-bottom: none;
    }

    tbody tr:hover {
      background: rgba(229, 192, 123, 0.03);
    }

    tbody td {
      padding: 16px 20px;
      font-size: 13px;
      color: var(--text-2);
      vertical-align: middle;
    }

    .asset-name {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .asset-icon {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      background: var(--gold-dim);
      border: 1px solid rgba(229, 192, 123, 0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 17px;
      color: var(--gold);
      flex-shrink: 0;
    }

    .asset-name-text {
      font-weight: 500;
      color: var(--text-1);
      font-size: 14px;
    }

    .asset-name-id {
      font-size: 11px;
      color: var(--text-3);
      margin-top: 2px;
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 0.04em;
    }

    .badge-available {
      background: var(--green-dim);
      color: var(--green);
      border: 1px solid rgba(74, 222, 128, 0.2);
    }

    .badge-borrowed {
      background: var(--red-dim);
      color: var(--red);
      border: 1px solid rgba(248, 113, 113, 0.2);
    }

    .badge-pending {
      background: var(--blue-dim);
      color: var(--blue);
      border: 1px solid rgba(96, 165, 250, 0.2);
    }

    .badge-maintenance {
      background: rgba(251, 191, 36, 0.1);
      color: #fbbf24;
      border: 1px solid rgba(251, 191, 36, 0.2);
    }

    .badge-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: currentColor;
    }

    .lender-info {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .lender-avatar {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background: rgba(229, 192, 123, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 10px;
      font-weight: 600;
      color: var(--gold);
      flex-shrink: 0;
    }

    .action-btns {
      display: flex;
      gap: 6px;
    }

    .action-btn {
      background: transparent;
      border: 1px solid var(--glass-border);
      padding: 6px 10px;
      border-radius: 8px;
      color: var(--text-3);
      font-size: 16px;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
    }

    .action-btn:hover {
      background: var(--gold-dim);
      border-color: rgba(229, 192, 123, 0.3);
      color: var(--gold);
    }

    .action-btn.danger:hover {
      background: var(--red-dim);
      border-color: rgba(248, 113, 113, 0.3);
      color: var(--red);
    }

    .action-btn.toggle-btn {
      font-size: 12px;
      padding: 6px 12px;
      font-family: 'Outfit', sans-serif;
      font-weight: 500;
      letter-spacing: 0.04em;
    }

    /* ── GRID VIEW ── */
    .asset-grid {
      display: none;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 20px;
    }

    .asset-card-grid {
      background: var(--glass);
      backdrop-filter: blur(16px);
      border: 1px solid var(--glass-border);
      border-radius: 14px;
      padding: 24px;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      position: relative;
      overflow: hidden;
    }

    .asset-card-grid:hover {
      transform: translateY(-4px);
      border-color: rgba(229, 192, 123, 0.25);
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
    }

    .asset-card-grid::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at top right, var(--gold-dim), transparent 60%);
      opacity: 0;
      transition: opacity 0.3s;
    }

    .asset-card-grid:hover::before {
      opacity: 1;
    }

    .grid-card-icon {
      width: 48px;
      height: 48px;
      border-radius: 10px;
      background: var(--gold-dim);
      border: 1px solid rgba(229, 192, 123, 0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: var(--gold);
      margin-bottom: 16px;
      position: relative;
      z-index: 1;
    }

    .grid-card-name {
      font-family: 'Cormorant Garamond', serif;
      font-size: 20px;
      font-weight: 500;
      color: var(--text-1);
      margin-bottom: 4px;
      position: relative;
      z-index: 1;
    }

    .grid-card-type {
      font-size: 12px;
      color: var(--text-3);
      margin-bottom: 12px;
      position: relative;
      z-index: 1;
    }

    .grid-card-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
      position: relative;
      z-index: 1;
    }

    .grid-card-actions {
      display: flex;
      gap: 8px;
      position: relative;
      z-index: 1;
    }

    /* SHOW/HIDE VIEW */
    body.grid-view .asset-table-wrap {
      display: none;
    }

    body.grid-view .asset-grid {
      display: grid;
    }

    /* ── MODAL ── */
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
      max-width: 540px;
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
      padding: 28px 32px 0;
      flex-shrink: 0;
    }

    .modal-body {
      padding: 24px 32px;
      overflow-y: auto;
      flex: 1;
    }

    .modal-body::-webkit-scrollbar {
      width: 4px;
    }

    .modal-body::-webkit-scrollbar-thumb {
      background: var(--glass-border);
      border-radius: 4px;
    }

    .modal-footer {
      padding: 16px 32px 28px;
      flex-shrink: 0;
      border-top: 1px solid var(--glass-border);
      background: rgba(15, 15, 20, 0.97);
    }

    .modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 0;
      padding-bottom: 20px;
      border-bottom: 1px solid var(--glass-border);
    }

    .modal-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 26px;
      font-weight: 500;
      color: var(--text-1);
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
      color: var(--red);
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
    .form-select,
    .form-textarea {
      width: 100%;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid var(--glass-border);
      border-radius: 10px;
      padding: 12px 16px;
      color: var(--text-1);
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      outline: none;
      transition: border-color 0.2s;
    }

    .form-input::placeholder,
    .form-textarea::placeholder {
      color: var(--text-3);
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
      border-color: rgba(229, 192, 123, 0.4);
    }

    .form-select {
      appearance: none;
      cursor: pointer;
    }

    .form-textarea {
      resize: vertical;
      min-height: 80px;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .form-timestamp {
      font-size: 12px;
      color: var(--text-3);
      margin-top: 6px;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .form-footer {
      display: flex;
      gap: 12px;
      margin-top: 0;
    }

    .btn-ghost {
      flex: 1;
      background: transparent;
      border: 1px solid var(--glass-border);
      padding: 12px;
      border-radius: 10px;
      color: var(--text-2);
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-ghost:hover {
      border-color: var(--gold);
      color: var(--gold);
    }

    .btn-submit {
      flex: 2;
      background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
      border: none;
      padding: 12px;
      border-radius: 10px;
      color: var(--bg-deep);
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      box-shadow: 0 4px 15px var(--gold-glow);
    }

    .btn-submit:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 20px var(--gold-glow);
    }

    /* DELETE CONFIRM MODAL */
    .delete-modal {
      max-width: 400px;
      text-align: center;
    }

    .delete-modal .delete-icon {
      width: 64px;
      height: 64px;
      border-radius: 16px;
      background: var(--red-dim);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 32px;
      color: var(--red);
      margin: 0 auto 20px;
    }

    .delete-modal p {
      color: var(--text-2);
      font-size: 14px;
      line-height: 1.6;
      margin-bottom: 28px;
    }

    .delete-modal .form-footer {
      justify-content: center;
    }

    .btn-danger {
      flex: 2;
      background: var(--red);
      border: none;
      padding: 12px;
      border-radius: 10px;
      color: #fff;
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    }

    .btn-danger:hover {
      background: #ef4444;
    }

    /* SUCCESS TOAST */
    .toast {
      position: fixed;
      bottom: 32px;
      right: 32px;
      background: rgba(15, 15, 20, 0.95);
      border: 1px solid rgba(74, 222, 128, 0.3);
      border-radius: 12px;
      padding: 16px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      color: var(--green);
      font-size: 14px;
      z-index: 300;
      transform: translateY(20px);
      opacity: 0;
      transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
      pointer-events: none;
    }

    .toast.active {
      transform: translateY(0);
      opacity: 1;
    }

    .toast i {
      font-size: 20px;
    }

    /* PAGINATION */
    .pagination {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 20px;
      border-top: 1px solid var(--glass-border);
    }

    .pagination-info {
      font-size: 12px;
      color: var(--text-3);
    }

    .pagination-btns {
      display: flex;
      gap: 6px;
    }

    .page-btn {
      background: transparent;
      border: 1px solid var(--glass-border);
      padding: 6px 12px;
      border-radius: 8px;
      color: var(--text-3);
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .page-btn:hover,
    .page-btn.active {
      background: var(--gold-dim);
      border-color: rgba(229, 192, 123, 0.3);
      color: var(--gold);
    }

    /* ── LENDING HISTORY INJECT ── */
    .data-panel {
      display: block;
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
    
    .header-actions { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
    .btn-outline {
      display: flex; align-items: center; gap: 8px;
      background: transparent; border: 1px solid var(--glass-border);
      padding: 12px 24px; border-radius: 30px; color: var(--text-2);
      font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 600;
      cursor: pointer; transition: all 0.3s;
    }
    .btn-outline:hover { border-color: var(--gold); color: var(--gold); background: var(--gold-dim); }

    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(24px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (max-width: 900px) {
      .stats-bar {
        grid-template-columns: repeat(2, 1fr);
      }

      .form-row {
        grid-template-columns: 1fr;
      }

      .page-header {
        flex-direction: column;
        align-items: flex-start;
      }
    }

    @media (max-width: 600px) {
      .stats-bar {
        grid-template-columns: 1fr 1fr;
      }

      table thead th:nth-child(4),
      table tbody td:nth-child(4),
      table thead th:nth-child(5),
      table tbody td:nth-child(5) {
        display: none;
      }
    }

    /* ── MODAL MOBILE ── */
    @media (max-width: 600px) {
      .modal-overlay {
        padding: 0;
        align-items: flex-end;
      }

      .modal {
        max-width: 100%;
        border-radius: 20px 20px 0 0;
        max-height: 92vh;
        margin: 0;
      }

      .modal-header {
        padding: 20px 20px 0;
      }

      .modal-body {
        padding: 16px 20px;
      }

      .modal-footer {
        padding: 12px 20px 20px;
      }

      .form-footer {
        flex-direction: row;
      }

      .btn-ghost,
      .btn-submit,
      .btn-danger {
        font-size: 13px;
        padding: 12px 10px;
      }
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

  <nav class="top-nav">
    <a href="borrower_lender_dashboard.php" class="nav-brand">
      <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="HariBorrow Logo" class="nav-logo">
      <span class="nav-title">HariBorrow</span>
    </a>

    <div class="nav-right">
      <a href="borrower_lender_dashboard.php" class="nav-back"><i class="ph ph-arrow-left"></i> Dashboard</a>

      <div class="profile-menu">
        <button class="profile-btn" onclick="toggleDropdown()">
          <div class="profile-avatar" id="navAvatar">UN</div>
          <span id="navUserName">User Name</span>
          <i class="ph ph-caret-down"></i>
        </button>
        <div class="dropdown" id="settingsDropdown">
          <button class="drop-item" onclick="window.location.href='my_profile.php'"><i class="ph ph-user"></i> My
            Profile</button>
          <button class="drop-item" onclick="window.location.href='profile_settings.php'"><i class="ph ph-gear"></i>
            Account Settings</button>
          <button class="drop-item" onclick="window.location.href='report_issue.php'"><i
              class="ph ph-warning-circle"></i> Report an Issue</button>
          <div class="drop-divider"></div>
          <button class="drop-item logout" onclick="window.api.removeToken(); window.location.href='login.php'"><i
              class="ph ph-sign-out"></i>
            Secure Log Out</button>
        </div>
      </div>
    </div>
  </nav>

  <main class="page">

    <div class="page-header">
      <div class="page-header-left">
        <h1>Asset Management</h1>
        <p>View, create, toggle availability, and monitor your uploaded equipment.</p>
      </div>
      <div class="header-actions">
        <button class="btn-primary" id="btnAddAsset" onclick="openCreateModal()">
          <i class="ph ph-plus"></i> Add New Asset
        </button>
      </div>
    </div>

    <div class="page-nav">
      <button class="page-nav-btn active" id="tabAssets" onclick="switchMainTab('assets')">My Assets</button>
      <button class="page-nav-btn" id="tabHistory" onclick="switchMainTab('history')">Lending History</button>
    </div>

    <div id="assetsView" class="view-section active">
      <div class="stats-bar">
        <div class="stat-card">
          <div class="stat-icon gold"><i class="ph ph-stack"></i></div>
          <div class="stat-info">
            <div class="stat-value" id="statTotal">0</div>
            <div class="stat-label">Total Assets</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green"><i class="ph ph-check-circle"></i></div>
          <div class="stat-info">
            <div class="stat-value" id="statAvailable">0</div>
            <div class="stat-label">Available</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon red"><i class="ph ph-prohibit"></i></div>
          <div class="stat-info">
            <div class="stat-value" id="statUnavailable">0</div>
            <div class="stat-label">Unavailable</div>
          </div>
        </div>
      </div>

      <div class="toolbar">
        <div class="search-wrap">
          <i class="ph ph-magnifying-glass"></i>
          <input class="search-input" type="text" placeholder="Search by name, type, or ID…"
            oninput="filterAssets(this.value)">
        </div>
        <select class="filter-select" onchange="filterByType(this.value)">
          <option value="">All Types</option>
          <option value="Electronics">Electronics</option>
          <option value="Mechanical">Mechanical</option>
          <option value="Laboratory">Laboratory</option>
          <option value="Computing">Computing</option>
        </select>
        <select class="filter-select" onchange="filterByStatus(this.value)">
          <option value="">All Status</option>
          <option value="Available">Available</option>
          <option value="Borrowed">Borrowed</option>
          <option value="Unavailable">Unavailable</option>
        </select>
        <div class="view-toggle">
          <button class="view-btn active" onclick="setView('table', this)" title="Table view"><i
              class="ph ph-list"></i></button>
          <button class="view-btn" onclick="setView('grid', this)" title="Grid view"><i
              class="ph ph-squares-four"></i></button>
        </div>
      </div>

      <div class="asset-table-wrap">
        <table id="assetTable">
          <thead>
            <tr>
              <th class="sortable" onclick="sortTable('name')">Asset <i class="ph ph-arrows-down-up sort-icon"></i></th>
              <th class="sortable" onclick="sortTable('type')">Type <i class="ph ph-arrows-down-up sort-icon"></i></th>
              <th class="sortable" onclick="sortTable('created')">Time Created <i
                  class="ph ph-arrows-down-up sort-icon"></i></th>
              <th>Lender's Info</th>
              <th class="sortable" onclick="sortTable('status')">Availability <i
                  class="ph ph-arrows-down-up sort-icon"></i></th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="assetTableBody"></tbody>
        </table>
        <div class="pagination">
          <span class="pagination-info" id="paginationInfo"></span>
          <div class="pagination-btns">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn"><i class="ph ph-caret-right"></i></button>
          </div>
        </div>
      </div>

      <div class="asset-grid" id="assetGrid"></div>
    </div>

    <div id="historyView" class="view-section">
      <div class="data-panel" id="historyPanel">
        <div class="panel-header" style="flex-direction: column; align-items: flex-start; gap: 16px;">
          <div class="panel-title">Lending History Log</div>
          <div class="role-toggle" style="background: rgba(0,0,0,0.4); border: 1px solid var(--glass-border); border-radius: 40px; padding: 4px; display: inline-flex;">
            <button class="role-btn active" id="tabAll" onclick="switchHistorySubTab('all')" style="background: transparent; border: none; padding: 8px 16px; border-radius: 30px; color: var(--gold-light); cursor: pointer; font-family: 'Outfit', sans-serif; font-size: 13px;">All History</button>
            <button class="role-btn" id="tabActive" onclick="switchHistorySubTab('active')" style="background: transparent; border: none; padding: 8px 16px; border-radius: 30px; color: var(--text-2); cursor: pointer; font-family: 'Outfit', sans-serif; font-size: 13px;">Active Borrows</button>
          </div>
        </div>
        <div style="overflow-x:auto;">
          <table>
            <thead>
              <tr>
                <th>Asset</th>
                <th>Borrower</th>
                <th>Status</th>
                <th>Borrow Date</th>
                <th>Return Due</th>
                <th>Returned At</th>
                <th>Penalty</th>
                <th>Rating</th>
              </tr>
            </thead>
            <tbody id="historyTableBody">
              <tr><td colspan="8" style="padding:16px;color:var(--text-3);text-align:center;">Loading your lending history...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </main>

  <div class="modal-overlay" id="createModal">
    <div class="modal">
      <div class="modal-header">
        <div class="modal-title" id="modalTitle">Add New Asset</div>
        <button class="modal-close" onclick="closeModal('createModal')"><i class="ph ph-x"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Asset Name</label>
          <input class="form-input" id="assetName" type="text" placeholder="e.g. Oscilloscope Unit A">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Asset Type</label>
            <select class="form-select" id="assetType">
              <option>Electronics</option>
              <option>Mechanical</option>
              <option>Laboratory</option>
              <option>Computing</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Initial Status</label>
            <select class="form-select" id="assetStatus">
              <option>Available</option>
              <option>Unavailable</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Lender / Department</label>
          <input class="form-input" id="assetLender" type="text" placeholder="e.g. Electrical Engineering Dept.">
        </div>
        <div class="form-group">
          <label class="form-label">Description (optional)</label>
          <textarea class="form-textarea" id="assetDesc" placeholder="Brief description of this asset…"></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Designated Meetup Location</label>
          <input class="form-input" id="assetMeetupLocation" type="text" placeholder="e.g. Engineering Lobby, Room 201">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Penalty Amount (PHP)</label>
            <input class="form-input" id="assetPenalty" type="number" min="0" max="10000" step="1" value="0"
              placeholder="0">
          </div>
          <div class="form-group">
            <label class="form-label">Penalty Type</label>
            <select class="form-select" id="assetPenaltyType">
              <option value="per_day">Per Day</option>
              <option value="per_hour">Per Hour</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Asset Photo (optional)</label>
          <div id="assetImagePreviewWrap"
            style="display:none; margin-bottom:12px; position:relative; width:120px; height:120px; border-radius:12px; overflow:hidden; border:1px solid var(--glass-border);">
            <img id="assetImagePreview" src="" alt="Preview" style="width:100%;height:100%;object-fit:cover;">
            <button type="button" onclick="clearAssetImage()"
              style="position:absolute;top:4px;right:4px;width:22px;height:22px;border-radius:50%;background:rgba(0,0,0,0.7);border:1px solid var(--glass-border);color:var(--danger);font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;"><i
                class="ph ph-x"></i></button>
          </div>
          <label for="assetImageInput"
            style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.04);border:1px dashed var(--glass-border);border-radius:10px;padding:14px 20px;cursor:pointer;color:var(--text-2);font-size:13px;transition:all 0.2s;width:100%;justify-content:center;"
            onmouseover="this.style.borderColor='rgba(229,192,123,0.4)'"
            onmouseout="this.style.borderColor='var(--glass-border)'">
            <i class="ph ph-camera" style="font-size:20px;color:var(--gold);"></i>
            <span id="assetImageLabel">Click to upload a photo of the asset</span>
          </label>
          <input type="file" id="assetImageInput" accept="image/jpeg,image/png,image/gif,image/webp"
            style="display:none;" onchange="previewAssetImage(this)">
        </div>
        <div class="form-timestamp">
          <i class="ph ph-clock"></i>
          <span id="timestampDisplay">Timestamp will be auto-generated on save</span>
        </div>
      </div>
      <div class="modal-footer">
        <div class="form-footer">
          <button class="btn-ghost" onclick="closeModal('createModal')">Cancel</button>
          <button class="btn-submit" onclick="saveAsset()">Create Asset</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="deleteModal">
    <div class="modal delete-modal">
      <div class="delete-icon"><i class="ph ph-trash"></i></div>
      <div class="modal-title" style="margin-bottom:12px;">Delete Asset?</div>
      <p>This action is permanent. The asset will be removed from the database and all related borrow records will be
        archived.</p>
      <div class="form-footer">
        <button class="btn-ghost" onclick="closeModal('deleteModal')">Cancel</button>
        <button class="btn-danger" onclick="confirmDelete()">Yes, Delete</button>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="ratingModal">
    <div class="modal">
      <div class="modal-header">
        <h2 class="modal-title">Rate Borrower</h2>
      </div>
      <div class="modal-body">
        <div style="color: var(--text-2); font-size: 13px; margin-bottom: 16px;" id="ratingModalCounterparty">Rate your transaction counterpart.</div>
        <input type="hidden" id="ratingTxnId">
        <div class="form-group">
          <label class="form-label" for="ratingValueInput">Rating (1-5)</label>
          <select class="form-select" id="ratingValueInput">
            <option value="5">5 - Excellent</option>
            <option value="4">4 - Good</option>
            <option value="3">3 - Okay</option>
            <option value="2">2 - Poor</option>
            <option value="1">1 - Very Poor</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="ratingCommentInput">Comment (optional)</label>
          <textarea class="form-textarea" id="ratingCommentInput" placeholder="How was this borrower?"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <div class="form-footer">
          <button class="btn-ghost" onclick="closeModal('ratingModal')">Cancel</button>
          <button class="btn-submit" onclick="submitRating()">Submit Rating</button>
        </div>
      </div>
    </div>
  </div>

  <div class="toast" id="toast">
    <i class="ph ph-check-circle"></i>
    <span id="toastMsg">Asset created successfully.</span>
  </div>

  <script>
    let assets = [];
    let filteredAssets = [];
    let deleteTarget = null;
    let searchQ = '', typeFilter = '', statusFilter = '';

    const iconMap = { electronics: 'ph-wave-sine', mechanical: 'ph-gear-six', laboratory: 'ph-flask', computing: 'ph-cpu' };

    function esc(v) {
      return String(v ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function badgeText(asset) {
      const status = (asset.status || '').toLowerCase();
      if (status === 'pending') return ['badge-pending', 'Pending Approval'];
      if (status === 'rejected') return ['badge-borrowed', 'Rejected'];
      return asset.availability === 'available'
        ? ['badge-available', 'Approved • Available']
        : ['badge-maintenance', 'Approved • Unavailable'];
    }

    async function loadAssets() {
      try {
        const data = await window.api.authenticatedFetch('/assets/manage_my_assets.php');
        const list = Array.isArray(data?.assets) ? data.assets : [];
        assets = list.map(a => {
          const n = (a.name || 'Asset').trim();
          const initials = n.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase() || 'AS';
          const type = a.type || 'General';
          return {
            id: a.id,
            name: n,
            type,
            created: a.created_at || '',
            meetupLocation: a.meetup_location || '—',
            proposedPenaltyAmount: Number(a.proposed_penalty_amount || 0),
            dailyPenalty: Number(a.daily_penalty || 0),
            penaltyType: a.penalty_type || 'per_day',
            lender: 'My Upload',
            lenderInitials: initials,
            status: (a.status || 'pending').toLowerCase(),
            availability: (a.availability || 'unavailable').toLowerCase(),
            icon: iconMap[String(type).toLowerCase()] || 'ph-stack'
          };
        });
        applyFilters();
      } catch (error) {
        console.error('Failed to load my assets', error);
        showToast('Failed to load assets.');
      }
    }

    function renderTable() {
      const tbody = document.getElementById('assetTableBody');
      if (!filteredAssets.length) {
        tbody.innerHTML = `<tr><td colspan="6" style="padding:20px;color:var(--text-3);">No assets found.</td></tr>`;
        document.getElementById('paginationInfo').textContent = 'Showing 0 assets';
        return;
      }

      tbody.innerHTML = filteredAssets.map(a => {
        const [badgeClass, badgeLabel] = badgeText(a);
        const canToggle = a.status === 'approved';
        return `
          <tr>
            <td>
              <div class="asset-name">
                <div class="asset-icon"><i class="ph ${a.icon}"></i></div>
                <div>
                  <div class="asset-name-text">${esc(a.name)}</div>
                  <div class="asset-name-id">ID: ${esc(a.id)}</div>
                </div>
              </div>
            </td>
            <td>${esc(a.type)}</td>
            <td style="white-space:nowrap">${esc(a.created)}</td>
            <td>
              <div class="lender-info">
                <div class="lender-avatar">${esc(a.lenderInitials)}</div>
                <span>${esc(a.lender)}</span>
              </div>
            </td>
            <td><span class="badge ${badgeClass}"><span class="badge-dot"></span>${esc(badgeLabel)}</span></td>
            <td>
              <div class="action-btns">
                <button class="action-btn toggle-btn" ${canToggle ? '' : 'disabled style="opacity:.45;cursor:not-allowed;"'} onclick="toggleAvailability(${a.id})">
                  ${a.availability === 'available' ? 'Set Unavailable' : 'Set Available'}
                </button>
                <button class="action-btn danger" onclick="openDeleteModal(${a.id})"><i class="ph ph-trash"></i></button>
              </div>
            </td>
          </tr>
        `;
      }).join('');
      document.getElementById('paginationInfo').textContent = `Showing ${filteredAssets.length} of ${assets.length} assets`;
    }

    function renderGrid() {
      const grid = document.getElementById('assetGrid');
      grid.innerHTML = filteredAssets.map(a => {
        const [badgeClass, badgeLabel] = badgeText(a);
        const canToggle = a.status === 'approved';
        return `
          <div class="asset-card-grid">
            <div class="grid-card-icon"><i class="ph ${a.icon}"></i></div>
            <div class="grid-card-name">${esc(a.name)}</div>
            <div class="grid-card-type">${esc(a.type)} · ${esc(a.id)}</div>
            <div class="grid-card-meta">${`<span class="badge ${badgeClass}"><span class="badge-dot"></span>${esc(badgeLabel)}</span>`}</div>
            <div style="font-size:11px;color:var(--text-3);margin-bottom:16px;">${esc(a.created)}</div>
            <div class="grid-card-actions">
              <button class="action-btn toggle-btn" style="flex:1;justify-content:center" ${canToggle ? '' : 'disabled style="opacity:.45;cursor:not-allowed;"'} onclick="toggleAvailability(${a.id})">
                ${a.availability === 'available' ? 'Set Unavailable' : 'Set Available'}
              </button>
              <button class="action-btn danger" onclick="openDeleteModal(${a.id})"><i class="ph ph-trash"></i></button>
            </div>
          </div>
        `;
      }).join('');
    }

    function updateStats() {
      document.getElementById('statTotal').textContent = assets.length;
      document.getElementById('statAvailable').textContent = assets.filter(a => a.availability === 'available').length;
      document.getElementById('statUnavailable').textContent = assets.filter(a => a.availability !== 'available').length;
    }

    function renderAll() { renderTable(); renderGrid(); updateStats(); }
    function applyFilters() {
      filteredAssets = assets.filter(a => {
        const q = searchQ.toLowerCase();
        const matchQ = !q || a.name.toLowerCase().includes(q) || a.type.toLowerCase().includes(q) || String(a.id).toLowerCase().includes(q);
        const matchT = !typeFilter || String(a.type).toLowerCase() === String(typeFilter).toLowerCase();
        const lifecycleStatus = a.status === 'approved' ? (a.availability === 'available' ? 'Available' : 'Borrowed') : (a.status === 'pending' ? 'Pending' : 'Maintenance');
        const matchS = !statusFilter || lifecycleStatus === statusFilter;
        return matchQ && matchT && matchS;
      });
      renderAll();
    }
    function filterAssets(q) { searchQ = q; applyFilters(); }
    function filterByType(t) { typeFilter = t; applyFilters(); }
    function filterByStatus(s) { statusFilter = s; applyFilters(); }

    let sortDir = {};
    function sortTable(key) {
      sortDir[key] = !sortDir[key];
      filteredAssets.sort((a, b) => {
        const av = key === 'name' ? a.name : key === 'type' ? a.type : key === 'created' ? a.created : `${a.status}:${a.availability}`;
        const bv = key === 'name' ? b.name : key === 'type' ? b.type : key === 'created' ? b.created : `${b.status}:${b.availability}`;
        return sortDir[key] ? String(av).localeCompare(String(bv)) : String(bv).localeCompare(String(av));
      });
      renderAll();
    }

    async function toggleAvailability(id) {
      const a = assets.find(x => x.id === id);
      if (!a || a.status !== 'approved') return;
      const newAvailability = a.availability === 'available' ? 'unavailable' : 'available';
      try {
        await window.api.authenticatedFetch('/assets/manage_my_assets.php', {
          method: 'PUT',
          body: { id, availability: newAvailability }
        });
        a.availability = newAvailability;
        applyFilters();
        showToast(`"${a.name}" availability updated.`);
      } catch (error) {
        alert('Failed to update availability: ' + (error.message || 'Server error'));
      }
    }

    function openCreateModal() {
      document.getElementById('modalTitle').textContent = 'Add New Asset';
      document.getElementById('assetName').value = '';
      document.getElementById('assetDesc').value = '';
      document.getElementById('assetMeetupLocation').value = '';
      document.getElementById('assetPenalty').value = '0';
      document.getElementById('assetPenaltyType').value = 'per_day';
      document.getElementById('assetImageInput').value = '';
      document.getElementById('assetImagePreviewWrap').style.display = 'none';
      document.getElementById('assetImageLabel').textContent = 'Click to upload a photo of the asset';
      document.getElementById('timestampDisplay').textContent = 'Will be stamped: ' + new Date().toLocaleString();
      document.getElementById('createModal').classList.add('active');
    }

    function previewAssetImage(input) {
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
          document.getElementById('assetImagePreview').src = e.target.result;
          document.getElementById('assetImagePreviewWrap').style.display = 'block';
          document.getElementById('assetImageLabel').textContent = input.files[0].name;
        };
        reader.readAsDataURL(input.files[0]);
      }
    }

    function clearAssetImage() {
      document.getElementById('assetImageInput').value = '';
      document.getElementById('assetImagePreviewWrap').style.display = 'none';
      document.getElementById('assetImageLabel').textContent = 'Click to upload a photo of the asset';
    }

    async function saveAsset() {
      const name = document.getElementById('assetName').value.trim();
      const type = document.getElementById('assetType').value;
      const description = document.getElementById('assetDesc').value.trim();
      const meetup_location = document.getElementById('assetMeetupLocation').value.trim();
      const proposed_penalty_amount = Math.min(10000, Math.max(0, Math.floor(Number(document.getElementById('assetPenalty').value || 0))));
      const daily_penalty = proposed_penalty_amount;
      const penalty_type = document.getElementById('assetPenaltyType').value;
      if (!name) { alert('Please fill in Asset Name.'); return; }

      try {
        // 1. Create the asset record
        const res = await window.api.authenticatedFetch('/assets/add_asset.php', {
          method: 'POST',
          body: { name, type, description, meetup_location, proposed_penalty_amount, daily_penalty, penalty_type }
        });

        // 2. If an image was selected, upload it
        const imageFile = document.getElementById('assetImageInput').files[0];
        if (imageFile && res?.asset_id) {
          const formData = new FormData();
          formData.append('asset_id', res.asset_id);
          formData.append('asset_image', imageFile);

          const token = window.api.getToken();
          try {
            await fetch('/SD_FINALPROJECT_GRP6/HariBorrow_backend/api/assets/upload_asset_image.php', {
              method: 'POST',
              headers: { 'Authorization': `Bearer ${token}` },
              body: formData
            }).then(r => r.json());
          } catch (imgErr) {
            console.warn('Image upload failed, but asset was created:', imgErr);
          }
        }

        closeModal('createModal');
        showToast(`"${name}" submitted for approval.`);
        await loadAssets();
      } catch (error) {
        alert("Error saving asset: " + (error.message || 'Unknown error'));
      }
    }

    function openDeleteModal(id) { deleteTarget = id; document.getElementById('deleteModal').classList.add('active'); }
    async function confirmDelete() {
      if (!deleteTarget) return;
      try {
        await window.api.authenticatedFetch('/assets/manage_my_assets.php', { method: 'DELETE', body: { id: deleteTarget } });
        showToast('Asset deleted successfully.');
        deleteTarget = null;
        closeModal('deleteModal');
        await loadAssets();
      } catch (error) {
        alert('Failed to delete asset: ' + (error.message || 'Unknown error'));
      }
    }

    function closeModal(id) { document.getElementById(id).classList.remove('active'); }
    document.querySelectorAll('.modal-overlay').forEach(m => m.addEventListener('click', function (e) { if (e.target === this) this.classList.remove('active'); }));
    function setView(v, btn) { document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active')); btn.classList.add('active'); document.body.classList.toggle('grid-view', v === 'grid'); }
    function showToast(msg) { const t = document.getElementById('toast'); document.getElementById('toastMsg').textContent = msg; t.classList.add('active'); setTimeout(() => t.classList.remove('active'), 3200); }
    function toggleDropdown() { document.getElementById('settingsDropdown').classList.toggle('active'); }
    document.addEventListener('click', e => { if (!document.querySelector('.profile-menu').contains(e.target)) document.getElementById('settingsDropdown').classList.remove('active'); });
    document.addEventListener('mousemove', e => { document.getElementById('glow').style.setProperty('--mouse-x', e.clientX + 'px'); document.getElementById('glow').style.setProperty('--mouse-y', e.clientY + 'px'); });

    /* ── TABS NAVIGATION LOGIC ── */
    function switchMainTab(tab) {
      document.querySelectorAll('.page-nav-btn').forEach(btn => btn.classList.remove('active'));
      document.querySelectorAll('.view-section').forEach(view => view.classList.remove('active'));
      
      if (tab === 'history') {
        document.getElementById('tabHistory').classList.add('active');
        document.getElementById('historyView').classList.add('active');
        document.getElementById('btnAddAsset').style.display = 'none';
        renderHistoryPanel();
      } else {
        document.getElementById('tabAssets').classList.add('active');
        document.getElementById('assetsView').classList.add('active');
        document.getElementById('btnAddAsset').style.display = 'flex';
      }
    }

    /* ── LENDING HISTORY LOGIC ── */
    let myLendings = [];
    let lendingsById = {};
    let currentHistoryTab = 'all';

    function switchHistorySubTab(tab) {
      currentHistoryTab = tab;
      document.getElementById('tabAll').style.color = tab === 'all' ? 'var(--gold-light)' : 'var(--text-2)';
      document.getElementById('tabActive').style.color = tab === 'active' ? 'var(--gold-light)' : 'var(--text-2)';
      renderHistoryPanel();
    }

    function getStatusData(tx) {
      const raw = String(tx?.status || '').toLowerCase();
      if (tx?.dates?.returned) return { label: 'Returned', class: 'active' };
      if (tx?.is_overdue) return { label: 'Overdue', class: 'overdue' };
      if (raw === 'approved' || raw === 'confirmed' || raw === 'active') return { label: 'Active', class: 'active' };
      if (raw === 'rejected') return { label: 'Rejected', class: 'overdue' };
      return { label: 'Pending', class: 'pending' };
    }

    function fmtDateSafe(v) {
      if (!v) return '—';
      const d = new Date(v);
      return Number.isNaN(d.getTime()) ? String(v) : d.toLocaleString();
    }

    async function loadMyLendings() {
      try {
        const res = await window.api.authenticatedFetch('/transactions/history.php');
        const history = Array.isArray(res?.history) ? res.history : [];
        myLendings = history.filter(tx => tx?.is_current_user_lender === true).sort((a, b) => {
          const aTs = new Date(a?.dates?.borrowed || a?.dates?.requested || 0).getTime() || 0;
          const bTs = new Date(b?.dates?.borrowed || b?.dates?.requested || 0).getTime() || 0;
          return bTs - aTs;
        });

        lendingsById = {};
        myLendings.forEach(tx => {
          lendingsById[String(tx?.transaction_id)] = tx;
        });
      } catch (error) {
        console.error('Failed to load my lendings:', error);
      }
    }

    function renderHistoryPanel() {
      const tbody = document.getElementById('historyTableBody');
      if (!tbody) return;

      let filteredHistory = myLendings;

      if (currentHistoryTab === 'active') {
        filteredHistory = filteredHistory.filter(tx => {
          const st = String(tx?.status || '').toLowerCase();
          const returned = Boolean(tx?.dates?.returned);
          return ((st === 'approved' || st === 'confirmed' || st === 'active') && !returned) || st === 'pending' || (st === 'overdue' && !returned);
        });
      }

      if (!filteredHistory.length) {
        tbody.innerHTML = '<tr><td colspan="8" style="padding: 24px; color: var(--text-3); text-align: center;">You have no ' + (currentHistoryTab === 'active' ? 'active borrows' : 'lending history') + ' yet.</td></tr>';
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
      const tx = lendingsById[String(transactionId)];
      if (!tx) return;
      document.getElementById('ratingTxnId').value = String(transactionId);
      document.getElementById('ratingCommentInput').value = '';
      document.getElementById('ratingValueInput').value = '5';
      document.getElementById('ratingModalCounterparty').textContent = `Rate ${tx?.counterparty?.name || 'this user'} for transaction #TXN-${transactionId}.`;
      document.getElementById('ratingModal').classList.add('active');
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
          body: { transaction_id: transactionId, rating, review_text: reviewText }
        });
        alert(res?.message || 'Rating submitted.');
        closeModal('ratingModal');
        await loadMyLendings();
        if (document.getElementById('historyView')?.classList.contains('active')) {
          renderHistoryPanel();
        }
      } catch (e) {
        alert(e?.message || 'Failed to submit rating.');
      }
    }

    document.addEventListener('DOMContentLoaded', async () => {
      await loadAssets();
      await loadMyLendings();

      if (new URLSearchParams(window.location.search).get('view') === 'history') {
        switchMainTab('history');
      }

      // Keep lending history fresh
      setInterval(async () => {
        await loadMyLendings();
        if (document.getElementById('historyView')?.classList.contains('active')) {
          renderHistoryPanel();
        }
      }, 15000);

      // Auto-open the Add Asset modal when arriving from the "Submit Asset" dashboard card
      if (new URLSearchParams(window.location.search).get('action') === 'submit') {
        openCreateModal();
      }
    });
  </script>
</body>

</html>