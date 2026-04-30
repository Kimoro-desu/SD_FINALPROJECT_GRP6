<?php
// PHP block kept empty for any future auth checks
$loansForJs = []; // We now use the JWT API in JS to populate loans securely
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HariBorrow — Return Asset</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
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
    --success:      #4ade80;
    --warning:      #facc15;
    --danger:       #f87171;
    --info:         #60a5fa;
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
    background: radial-gradient(480px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(229, 192, 123, 0.1), rgba(229, 192, 123, 0.03) 38%, transparent 68%);
    z-index: 9999; mix-blend-mode: screen; transition: background 0.08s ease-out;
  }

  /* ── SIDEBAR ── */
  .sidebar {
    width: var(--sidebar-w); height: 100vh;
    background: var(--glass-heavy); backdrop-filter: blur(24px);
    border-right: 1px solid var(--glass-border);
    display: flex; flex-direction: column; z-index: 100; position: relative; flex-shrink: 0;
  }

  .sidebar-header {
    padding: 32px 24px; border-bottom: 1px solid var(--glass-border);
    display: flex; align-items: center; gap: 12px;
  }

  .nav-logo {
    height: 48px; width: auto; object-fit: contain;
    filter: drop-shadow(0 0 10px rgba(229,192,123,0.4)) brightness(1.2) contrast(1.3) saturate(1.4);
    transition: transform 0.3s ease, filter 0.3s ease;
  }
  .nav-logo:hover {
    transform: scale(1.05);
    filter: drop-shadow(0 0 18px var(--gold)) brightness(1.4) contrast(1.4) saturate(1.6);
  }

  .nav-title {
    font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 600;
    background: linear-gradient(135deg, #FFF 0%, var(--gold-light) 50%, var(--gold-dark) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 0.02em;
    line-height: 1;
  }

  .user-badge {
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
    font-size: 14px; font-weight: 400; transition: all 0.3s; border: 1px solid transparent;
    position: relative;
  }
  .nav-link i { font-size: 20px; color: var(--text-3); transition: color 0.2s; }
  .nav-link:hover { background: rgba(255,255,255,0.03); color: var(--text-1); }
  .nav-link.active { background: var(--gold-dim); border-color: rgba(229,192,123,0.2); color: var(--gold-light); }
  .nav-link.active i { color: var(--gold); }

  /* ── NOTIFICATION NAV HIGHLIGHT (like popular apps) ── */
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

  .notif-badge-pill {
    margin-left: auto;
    background: #ef4444;
    color: #fff;
    padding: 2px 7px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 700;
    min-width: 18px;
    text-align: center;
    animation: badgePulse 2s ease-in-out infinite;
    box-shadow: 0 0 8px rgba(239, 68, 68, 0.4);
  }

  @keyframes badgePulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 8px rgba(239, 68, 68, 0.4); }
    50% { transform: scale(1.1); box-shadow: 0 0 14px rgba(239, 68, 68, 0.6); }
  }

  .notif-dot {
    position: absolute;
    top: 10px;
    left: 28px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ef4444;
    box-shadow: 0 0 6px rgba(239, 68, 68, 0.6);
    animation: dotPing 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
  }

  @keyframes dotPing {
    0% { transform: scale(1); opacity: 1; }
    75%, 100% { transform: scale(1.8); opacity: 0; }
  }

  .sidebar-footer { padding: 24px; border-top: 1px solid var(--glass-border); }
  .user-profile { display: flex; align-items: center; gap: 12px; }
  .user-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--gold-dark); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; color: var(--bg-deep); }
  .user-info { display: flex; flex-direction: column; }
  .user-name { font-size: 14px; font-weight: 500; color: var(--text-1); }
  .user-dept { font-size: 11px; color: var(--text-3); }
  
  /* ── NOTIFICATIONS DROPDOWN ── */
  .notif-dropdown.active {
      opacity: 1 !important;
      pointer-events: auto !important;
      transform: translateX(0) !important;
  }
  .notif-item {
    padding: 16px; border-bottom: 1px solid var(--glass-border);
    display: flex; gap: 12px; align-items: flex-start;
    text-decoration: none; transition: background 0.2s;
    cursor: pointer; color: inherit;
  }
  .notif-item:hover { background: rgba(255, 255, 255, 0.05); }
  .notif-item.unread { background: rgba(239, 68, 68, 0.04); border-left: 3px solid #ef4444; }
  .notif-item.unread:hover { background: rgba(239, 68, 68, 0.08); }
  .notif-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
  .notif-icon.info { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
  .notif-icon.danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
  .notif-icon.warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
  .notif-content { display: flex; flex-direction: column; gap: 4px; }
  .notif-title { font-size: 13px; font-weight: 500; color: var(--text-1); }
  .notif-desc { font-size: 12px; color: var(--text-2); line-height: 1.4; }
  .notif-time { font-size: 11px; color: var(--text-3); margin-top: 4px; }

  /* ── MAIN CONTENT ── */
  .main-content {
    flex-grow: 1; height: 100vh; overflow-y: auto; position: relative; z-index: 10;
    padding: 40px 48px; display: flex; flex-direction: column;
  }

  .header-area { margin-bottom: 36px; }
  .page-title h1 { font-family: 'Cormorant Garamond', serif; font-size: 42px; font-weight: 400; color: var(--text-1); line-height: 1.1; margin-bottom: 8px; }
  .page-title p { font-size: 14px; color: var(--text-2); font-weight: 300; letter-spacing: 0.03em; }

  /* ── STEPPER ── */
  .stepper { display: flex; align-items: center; gap: 0; margin-bottom: 36px; }
  .step-item {
    display: flex; align-items: center; gap: 10px; font-size: 12px; font-weight: 500;
    color: var(--text-3); letter-spacing: 0.05em;
  }
  .step-circle {
    width: 28px; height: 28px; border-radius: 50%; border: 1px solid var(--glass-border);
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 600; background: transparent; color: var(--text-3);
    transition: all 0.3s;
  }
  .step-item.active .step-circle { background: var(--gold-dim); border-color: rgba(229,192,123,0.4); color: var(--gold); }
  .step-item.active { color: var(--gold-light); }
  .step-item.done .step-circle { background: var(--gold-dim); border-color: rgba(229,192,123,0.4); color: var(--gold); }
  .step-item.done { color: var(--text-2); }
  .step-line { flex: 1; height: 1px; background: var(--glass-border); margin: 0 12px; max-width: 60px; }
  .step-line.done { background: rgba(229,192,123,0.3); }

  /* ── CARD ── */
  .card {
    background: var(--glass); border: 1px solid var(--glass-border); border-radius: 16px;
    padding: 36px; backdrop-filter: blur(16px); flex: 1;
    max-width: 700px;
  }

  .step { display: none; }
  .step.active { display: block; animation: fadeIn 0.35s ease forwards; }
  @keyframes fadeIn { from{opacity:0; transform:translateY(8px);} to{opacity:1; transform:translateY(0);} }

  .card-label {
    font-size: 10px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--text-3); margin-bottom: 6px;
  }
  .card-title {
    font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 500;
    color: var(--gold); margin-bottom: 8px; line-height: 1.1;
  }
  .card-desc { font-size: 13px; color: var(--text-2); font-weight: 300; margin-bottom: 28px; }

  /* ── ACTIVE LOANS LIST (STEP 1) ── */
  .loans-list { display: flex; flex-direction: column; gap: 12px; margin-bottom: 28px; }

  .loan-card {
    background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border);
    border-radius: 12px; padding: 16px 20px;
    cursor: pointer; transition: all 0.25s; position: relative;
    display: flex; align-items: center; gap: 16px;
  }
  .loan-card:hover { border-color: rgba(229,192,123,0.3); background: rgba(229,192,123,0.04); }
  .loan-card.selected { border-color: rgba(229,192,123,0.5); background: var(--gold-dim); }
  .loan-card.selected::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
    background: var(--gold); border-radius: 3px 0 0 3px;
  }

  .loan-icon {
    width: 44px; height: 44px; border-radius: 10px; background: rgba(229,192,123,0.08);
    border: 1px solid rgba(229,192,123,0.15);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  }
  .loan-icon i { font-size: 22px; color: var(--gold-dark); }

  .loan-details { flex: 1; }
  .loan-name { font-size: 15px; font-weight: 500; color: var(--text-1); margin-bottom: 4px; }
  .loan-meta { font-size: 12px; color: var(--text-3); display: flex; gap: 16px; flex-wrap: wrap; }
  .loan-meta span { display: flex; align-items: center; gap: 4px; }

  .loan-status {
    font-size: 11px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase;
    padding: 4px 10px; border-radius: 20px; flex-shrink: 0;
  }
  .status-active { background: rgba(74, 222, 128, 0.12); color: var(--success); border: 1px solid rgba(74,222,128,0.25); }
  .status-overdue { background: rgba(248, 113, 113, 0.12); color: var(--danger); border: 1px solid rgba(248,113,113,0.25); }
  .status-due-soon { background: rgba(250, 204, 21, 0.12); color: var(--warning); border: 1px solid rgba(250,204,21,0.25); }

  .loan-select-indicator {
    width: 20px; height: 20px; border-radius: 50%; border: 1.5px solid var(--glass-border);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    transition: all 0.2s;
  }
  .loan-card.selected .loan-select-indicator {
    background: var(--gold); border-color: var(--gold);
  }
  .loan-select-indicator i { font-size: 12px; color: #000; display: none; }
  .loan-card.selected .loan-select-indicator i { display: block; }

  /* ── ASSET INFO GRID ── */
  .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
  .info-item {
    padding: 14px 16px; border-bottom: 1px solid var(--glass-border);
    border-right: 1px solid var(--glass-border);
  }
  .info-item:nth-child(even) { border-right: none; }
  .info-item:nth-last-child(-n+2) { border-bottom: none; }
  .info-item.full { grid-column: 1 / -1; border-right: none; }
  .info-label { font-size: 10px; text-transform: uppercase; color: var(--text-3); letter-spacing: 0.12em; margin-bottom: 4px; }
  .info-value { font-size: 15px; color: var(--text-1); font-weight: 400; }
  .info-value.active { color: var(--success); }
  .info-value.overdue { color: var(--danger); }
  .info-value.due-soon { color: var(--warning); }
  .info-value.gold { color: var(--gold); }

  .info-wrap {
    background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border);
    border-radius: 12px; overflow: hidden; margin-bottom: 24px;
  }

  /* ── CONDITION SELECTOR ── */
  .condition-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; }
  .condition-option {
    padding: 14px 16px; border-radius: 10px; border: 1px solid var(--glass-border);
    background: rgba(255,255,255,0.02); cursor: pointer; transition: all 0.2s;
    display: flex; align-items: center; gap: 10px;
  }
  .condition-option:hover { border-color: rgba(229,192,123,0.3); background: rgba(229,192,123,0.03); }
  .condition-option.selected { border-color: rgba(229,192,123,0.5); background: var(--gold-dim); }
  .condition-option input[type="radio"] { display: none; }
  .condition-dot { width: 14px; height: 14px; border-radius: 50%; border: 1.5px solid var(--text-3); flex-shrink: 0; transition: all 0.2s; }
  .condition-option.selected .condition-dot { background: var(--gold); border-color: var(--gold); }
  .condition-label { font-size: 13px; color: var(--text-2); font-weight: 400; }
  .condition-option.selected .condition-label { color: var(--gold-light); }

  /* ── FORM ── */
  .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
  .form-label { font-size: 11px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-3); }
  .form-input, .form-textarea {
    background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border);
    border-radius: 10px; padding: 12px 14px; color: var(--text-1);
    font-family: 'Outfit', sans-serif; font-size: 13px; outline: none;
    transition: border-color 0.2s; width: 100%;
  }
  .form-input:focus, .form-textarea:focus { border-color: rgba(229,192,123,0.5); }
  .form-textarea { resize: vertical; min-height: 90px; }
  .form-input::placeholder, .form-textarea::placeholder { color: var(--text-3); }

  /* ── DURATION INFO BOX ── */
  .duration-display {
    background: rgba(229,192,123,0.06); border: 1px solid rgba(229,192,123,0.2);
    border-radius: 10px; padding: 12px 14px; font-size: 13px; color: var(--gold); font-weight: 500;
  }
  .duration-display span { color: var(--text-3); font-size: 11px; font-weight: 400; }

  /* ── OVERDUE BANNER ── */
  .overdue-banner {
    background: rgba(248,113,113,0.08); border: 1px solid rgba(248,113,113,0.25);
    border-radius: 10px; padding: 12px 16px; margin-bottom: 20px;
    display: flex; align-items: flex-start; gap: 10px;
    font-size: 13px; color: var(--danger); line-height: 1.5;
  }
  .overdue-banner i { font-size: 18px; margin-top: 1px; flex-shrink: 0; }
  .overdue-banner.hidden { display: none; }

  /* ── TERMS ── */
  .terms-wrap {
    display: flex; gap: 12px; align-items: flex-start;
    background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border);
    border-radius: 10px; padding: 14px; margin-top: 4px;
    font-size: 13px; color: var(--text-2); line-height: 1.5;
  }
  .terms-wrap input[type="checkbox"] { margin-top: 2px; accent-color: var(--gold); flex-shrink: 0; }

  /* ── SUMMARY (STEP 3) ── */
  .summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
  .summary-section { background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 12px; padding: 16px; }
  .summary-label { font-size: 10px; font-weight: 600; letter-spacing: 0.15em; text-transform: uppercase; color: var(--text-3); margin-bottom: 12px; border-bottom: 1px solid var(--glass-border); padding-bottom: 8px; }
  .summary-row { margin-bottom: 10px; }
  .summary-row span { display: block; font-size: 11px; color: var(--text-3); margin-bottom: 2px; }
  .summary-row strong { display: block; font-size: 14px; color: var(--text-1); font-weight: 400; }
  .notes-box {
    background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border);
    border-radius: 10px; padding: 14px 16px; font-size: 13px; color: var(--text-2); line-height: 1.6;
    margin-bottom: 24px;
  }

  /* ── SUCCESS ── */
  .success-icon { text-align: center; margin-bottom: 20px; }
  .success-icon i { font-size: 64px; color: var(--success); }

  /* ── BUTTONS ── */
  .btn-group { display: flex; gap: 12px; margin-top: 28px; }
  .btn {
    flex: 1; padding: 13px 20px; border-radius: 10px; cursor: pointer;
    font-family: 'Outfit', sans-serif; font-weight: 500; font-size: 14px;
    transition: all 0.25s; display: flex; align-items: center; justify-content: center; gap: 8px;
  }
  .btn-primary {
    background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
    border: none; color: #000; font-weight: 600;
  }
  .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 15px var(--gold-glow); }
  .btn-primary:disabled { opacity: 0.4; cursor: not-allowed; transform: none; box-shadow: none; }
  .btn-secondary {
    background: transparent; border: 1px solid var(--glass-border); color: var(--text-2);
  }
  .btn-secondary:hover { border-color: var(--gold); color: var(--gold); background: var(--gold-dim); }

  /* ── NO LOANS STATE ── */
  .empty-state {
    text-align: center; padding: 48px 24px;
    display: flex; flex-direction: column; align-items: center; gap: 12px;
  }
  .empty-state i { font-size: 48px; color: var(--text-3); }
  .empty-state h3 { font-family: 'Cormorant Garamond', serif; font-size: 22px; color: var(--text-2); font-weight: 400; }
  .empty-state p { font-size: 13px; color: var(--text-3); max-width: 280px; line-height: 1.5; }

  /* ── PHOTO UPLOAD ── */
  .photo-upload-area {
    border: 1px dashed var(--glass-border);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.25s;
    background: rgba(255,255,255,0.02);
    margin-bottom: 8px;
  }
  .photo-upload-area:hover {
    border-color: rgba(229,192,123,0.4);
    background: rgba(229,192,123,0.03);
  }
  .photo-upload-area i { font-size: 28px; color: var(--gold-dark); margin-bottom: 6px; }
  .photo-upload-area .upload-text { font-size: 13px; color: var(--text-2); }
  .photo-upload-area .upload-sub { font-size: 11px; color: var(--text-3); margin-top: 4px; }

  .photo-previews {
    display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;
  }
  .photo-preview-item {
    position: relative; width: 80px; height: 80px;
    border-radius: 10px; overflow: hidden;
    border: 1px solid var(--glass-border);
  }
  .photo-preview-item img {
    width: 100%; height: 100%; object-fit: cover;
  }
  .photo-preview-item .remove-photo {
    position: absolute; top: 2px; right: 2px;
    width: 20px; height: 20px; border-radius: 50%;
    background: rgba(0,0,0,0.7); border: 1px solid var(--glass-border);
    color: var(--danger); font-size: 10px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
  }
  .photo-preview-item .remove-photo:hover { background: var(--danger); color: #fff; }

  /* ── SUMMARY PHOTOS ── */
  .summary-photos {
    display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px;
  }
  .summary-photos img {
    width: 72px; height: 72px; object-fit: cover;
    border-radius: 8px; border: 1px solid var(--glass-border);
  }
</style>
</head>
<body>

<div class="bg-mesh"></div>
<div class="ambient-glow" id="glow"></div>

<aside class="sidebar">
  <div class="sidebar-header">
    <img src="../images/image_0.png" alt="HariBorrow Logo" class="nav-logo">
    <div>
      <div class="nav-title">HariBorrow</div>
      <span class="user-badge">User Portal</span>
    </div>
  </div>

  <nav class="nav-menu">
    <a href="borrower_lender_dashboard.php" class="nav-link"><i class="ph ph-squares-four"></i> My Dashboard</a>
    <div class="nav-section-title">Transactions</div>
    <a href="borrowing.php" class="nav-link"><i class="ph ph-package"></i> Borrow an Asset</a>
    <a href="#" class="nav-link active"><i class="ph ph-arrow-u-up-left"></i> Return an Asset</a>
    <a href="borrowing.php?view=borrows" class="nav-link"><i class="ph ph-clock-counter-clockwise"></i> My Borrowing History</a>
    
    <div style="position: relative;">
      <a href="notifications.php" class="nav-link" id="notifNavLink">
        <i class="ph ph-bell"></i> Notifications
        <span class="notif-badge-pill" id="notifBadge" style="display:none;">0</span>
      </a>
    </div>

    <div class="nav-section-title">Account</div>
    <a href="my_profile.php" class="nav-link"><i class="ph ph-user-circle"></i> My Profile</a>
    <a href="profile_settings.php" class="nav-link"><i class="ph ph-gear"></i> Account Settings</a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-profile">
      <div class="user-avatar" id="sidebarAvatar">UN</div>
      <div class="user-info">
        <span class="user-name" id="sidebarName">User Name</span>
        <span class="user-dept" id="sidebarRole">User Role</span>
      </div>
      <a onclick="window.api.removeToken(); window.location.href='login.php'" style="margin-left:auto;color:var(--text-3);cursor:pointer;" title="Secure Log Out">
        <i class="ph ph-sign-out" style="font-size:20px;"></i>
      </a>
    </div>
  </div>
</aside>

<main class="main-content">

  <div class="header-area">
    <div class="page-title">
      <h1>Return an Asset</h1>
      <p>Select a borrowed item to return and confirm its condition.</p>
    </div>
  </div>

  <div class="stepper">
    <div class="step-item active" id="stepItem1">
      <div class="step-circle">1</div> Select Asset
    </div>
    <div class="step-line" id="line1"></div>
    <div class="step-item" id="stepItem2">
      <div class="step-circle">2</div> Return Details
    </div>
    <div class="step-line" id="line2"></div>
    <div class="step-item" id="stepItem3">
      <div class="step-circle">3</div> Confirmation
    </div>
  </div>

  <div class="card">

    <div class="step active" id="step1">
      <div class="card-label">Step 1 of 3</div>
      <div class="card-title">Active Loans</div>
      <div class="card-desc">Select the asset you wish to return. Only currently borrowed items are shown below.</div>

      <div class="loans-list" id="loansList">
        <div class="empty-state" style="padding: 20px; text-align: center;">
          <i class="ph ph-spinner-gap" style="font-size: 32px; color: var(--text-3);"></i>
          <p style="color: var(--text-2); margin-top: 10px;">Loading active loans...</p>
        </div>
      </div>

      <div class="btn-group">
        <button class="btn btn-secondary" onclick="window.location.href='borrower_lender_dashboard.php'">
          <i class="ph ph-arrow-left"></i> Back to Dashboard
        </button>
        <button class="btn btn-primary" id="proceedBtn" onclick="goToStep(2)" disabled>
          <i class="ph ph-arrow-right"></i> Proceed
        </button>
      </div>
    </div>

    <div class="step" id="step2">
      <div class="card-label">Step 2 of 3</div>
      <div class="card-title">Return Details</div>
      <div class="card-desc">Confirm the condition of the asset and provide any relevant notes before submitting.</div>

      <div class="overdue-banner hidden" id="overdueBanner">
        <i class="ph ph-warning"></i>
        <span>This asset is <strong>overdue</strong>. Please return it to the designated location immediately and notify the lab custodian. Late returns are logged in your borrowing record.</span>
      </div>

      <div class="info-wrap">
        <div class="info-grid">
          <div class="info-item">
            <div class="info-label">Asset Name</div>
            <div class="info-value" id="r-name">—</div>
          </div>
          <div class="info-item">
            <div class="info-label">Serial / Asset Tag</div>
            <div class="info-value" id="r-tag">—</div>
          </div>
          <div class="info-item">
            <div class="info-label">Loan Status</div>
            <div class="info-value" id="r-status">—</div>
          </div>
          <div class="info-item">
            <div class="info-label">Due Date</div>
            <div class="info-value" id="r-due">—</div>
          </div>
          <div class="info-item full">
            <div class="info-label">Return Location</div>
            <div class="info-value gold" id="r-location">—</div>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label"><i class="ph ph-clock"></i> Return Date & Time</label>
        <div class="duration-display" id="returnDateTime">—</div>
      </div>

      <div class="form-group">
        <label class="form-label"><i class="ph ph-check-square"></i> Asset Condition Upon Return</label>
        <div class="condition-grid">
          <div class="condition-option" id="cond-good" onclick="selectCondition('Good')">
            <div class="condition-dot"></div>
            <span class="condition-label">Good — No damage</span>
          </div>
          <div class="condition-option" id="cond-fair" onclick="selectCondition('Fair')">
            <div class="condition-dot"></div>
            <span class="condition-label">Fair — Minor wear</span>
          </div>
          <div class="condition-option" id="cond-damaged" onclick="selectCondition('Damaged')">
            <div class="condition-dot"></div>
            <span class="condition-label">Damaged — Needs repair</span>
          </div>
          <div class="condition-option" id="cond-lost" onclick="selectCondition('Lost')">
            <div class="condition-dot"></div>
            <span class="condition-label">Lost / Missing</span>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label"><i class="ph ph-notepad"></i> Return Notes <span style="color:var(--text-3); font-weight:300; text-transform:none; letter-spacing:0;">(optional)</span></label>
        <textarea class="form-textarea" id="returnNotes" placeholder="Note any observations about the asset's condition, accessories returned, or other relevant remarks."></textarea>
      </div>

      <div class="form-group">
        <label class="form-label"><i class="ph ph-camera"></i> Upload Return Photos <span style="color:var(--text-3); font-weight:300; text-transform:none; letter-spacing:0;">(optional, up to 5)</span></label>
        <label for="returnPhotoInput" class="photo-upload-area">
          <i class="ph ph-cloud-arrow-up"></i>
          <div class="upload-text">Click to upload photos of the returned item</div>
          <div class="upload-sub">JPEG, PNG, GIF, or WebP — max 5MB each</div>
        </label>
        <input type="file" id="returnPhotoInput" accept="image/jpeg,image/png,image/gif,image/webp" multiple style="display:none;" onchange="previewReturnPhotos(this)">
        <div class="photo-previews" id="returnPhotoPreviews"></div>
      </div>

      <div class="terms-wrap">
        <input type="checkbox" id="terms">
        <label for="terms">I confirm that I am returning this asset to its designated location in the stated condition, and that all included accessories have been accounted for. I understand that any damage or loss will be reported to the department administrator.</label>
      </div>

      <div class="btn-group">
        <button class="btn btn-secondary" onclick="goToStep(1)">
          <i class="ph ph-arrow-left"></i> Back
        </button>
        <button class="btn btn-primary" onclick="handleSubmit()">
          <i class="ph ph-arrow-u-up-left"></i> Submit Return
        </button>
      </div>
    </div>

    <div class="step" id="step3">
      <div class="success-icon" style="color:var(--gold);">
        <i class="ph-fill ph-clock"></i>
      </div>
      <div class="card-label" style="text-align:center;">Return Submitted</div>
      <div class="card-title" style="text-align:center; color:var(--gold);">Pending Review</div>
      <div class="card-desc" style="text-align:center; margin-bottom:28px;">Your return request has been submitted. The lender will review the return photos and verify the asset condition before finalizing.</div>

      <div class="summary-grid">
        <div class="summary-section">
          <div class="summary-label">Asset Information</div>
          <div class="summary-row">
            <span>Asset Name</span>
            <strong id="s-name">—</strong>
          </div>
          <div class="summary-row">
            <span>Serial / Asset Tag</span>
            <strong id="s-tag">—</strong>
          </div>
          <div class="summary-row">
            <span>Return Location</span>
            <strong id="s-location" style="color:var(--gold);">—</strong>
          </div>
        </div>

        <div class="summary-section">
          <div class="summary-label">Return Record</div>
          <div class="summary-row">
            <span>Returned On</span>
            <strong id="s-datetime">—</strong>
          </div>
          <div class="summary-row">
            <span>Condition Reported</span>
            <strong id="s-condition" style="color:var(--gold);">—</strong>
          </div>
          <div class="summary-row">
            <span>Loan Status</span>
            <strong id="s-loan-status" style="color:var(--gold);">Pending Admin Review</strong>
          </div>
        </div>
      </div>

      <div class="summary-label" style="margin-bottom:8px;">Return Notes</div>
      <div class="notes-box" id="s-notes">—</div>

      <div id="s-photos-section" style="display:none; margin-bottom:24px;">
        <div class="summary-label" style="margin-bottom:8px;">Return Photos</div>
        <div class="summary-photos" id="s-photos"></div>
      </div>

      <div class="btn-group" style="margin-top:0;">
        <button class="btn btn-secondary" onclick="window.location.reload()">
          <i class="ph ph-arrow-counter-clockwise"></i> Return Another
        </button>
        <button class="btn btn-primary" onclick="window.location.href='borrower_lender_dashboard.php'">
          <i class="ph ph-squares-four"></i> Go to Dashboard
        </button>
      </div>
    </div>

    <!-- Toast Notification -->
    <div id="returnToast" style="position:fixed;bottom:32px;right:32px;background:rgba(15,15,20,0.95);border:1px solid rgba(229,192,123,0.3);border-radius:14px;padding:18px 28px;display:none;align-items:center;gap:12px;z-index:9999;box-shadow:0 8px 32px rgba(0,0,0,0.4);min-width:320px;">
      <i class="ph ph-check-circle" style="font-size:24px;color:var(--gold);"></i>
      <div>
        <div style="font-size:14px;font-weight:500;color:var(--text-1);" id="toastTitle">Return Submitted</div>
        <div style="font-size:12px;color:var(--text-2);margin-top:2px;" id="toastMsg">Pending admin review.</div>
      </div>
    </div>

  </div></main>

<script src="../js/api.js"></script>
<script src="../js/auth_guard.js"></script>
<script>
  /* ── GLOW ── */
  const glow = document.getElementById('glow');
  document.addEventListener('mousemove', e => {
    glow.style.setProperty('--mouse-x', e.clientX + 'px');
    glow.style.setProperty('--mouse-y', e.clientY + 'px');
  });

  /* ── NOTIFICATIONS LOGIC ── */
  // No dropdown to toggle or close anymore

  async function fetchNotifications() {
      try {
          const response = await window.api.authenticatedFetch('/transactions/notifications.php');
          if (response && response.status === 'success') {
              const notifs = response.notifications;
              const notifBadge = document.getElementById('notifBadge');
              const notifNavLink = document.getElementById('notifNavLink');
              
              if (notifs.length > 0) {
                  const unreadCount = notifs.filter(n => n.notification_id && !n.is_read).length;
                  notifBadge.style.display = unreadCount > 0 ? 'inline-block' : 'none';
                  notifBadge.textContent = unreadCount > 9 ? '9+' : unreadCount;
                  
                  if (notifNavLink) {
                      if (unreadCount > 0) {
                          notifNavLink.classList.add('has-notif');
                      } else {
                          notifNavLink.classList.remove('has-notif');
                      }
                  }
              } else {
                  notifBadge.style.display = 'none';
                  if (notifNavLink) notifNavLink.classList.remove('has-notif');
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




  /* ── LOAN DATA ── */
  let loans = {};

  let selectedLoan = null;
  let selectedCondition = null;

  /* ── LOAD LOANS VIA API ── */
  async function loadActiveLoans() {
    try {
      const res = await window.api.authenticatedFetch('/transactions/history.php');
      const history = Array.isArray(res?.history) ? res.history : [];

      const activeLoans = history.filter(tx =>
        String(tx?.status || '').toLowerCase() === 'approved' &&
        tx.is_current_user_borrower === true
      );

      const listEl = document.getElementById('loansList');
      listEl.innerHTML = '';

      if (activeLoans.length === 0) {
        listEl.innerHTML = `
          <div class="empty-state" style="padding: 20px; text-align: center;">
            <i class="ph ph-package" style="font-size: 32px; color: var(--text-3);"></i>
            <p style="color: var(--text-2); margin-top: 10px;">No active loans to return.</p>
          </div>
        `;
        return;
      }

      loans = {};
      activeLoans.forEach(loan => {
        const id = loan.transaction_id;
        const dueStr = loan.dates.due
          ? new Date(loan.dates.due.replace(' ', 'T')).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})
          : 'N/A';

        let isOverdue = false;
        let statusLabel = 'Active';
        let statusClass = 'active';

        if (loan.dates.due) {
          const due = new Date(loan.dates.due.replace(' ', 'T')).getTime();
          const now = Date.now();
          if (now > due) {
            isOverdue = true; statusLabel = 'Overdue'; statusClass = 'overdue';
          } else if (due - now < 86400 * 3 * 1000) {
            statusLabel = 'Due Soon'; statusClass = 'due-soon';
          }
        }

        loans[id] = {
          name: loan.asset.name,
          tag: 'AST-' + String(loan.asset.id).padStart(4, '0'),
          location: loan.asset.meetup_location || 'Designated Location',
          due: dueStr,
          statusLabel,
          statusClass,
          isOverdue
        };

        const card = document.createElement('div');
        card.className = 'loan-card';
        card.id = 'loan-' + id;
        card.onclick = () => selectLoan(id);
        card.innerHTML = `
          <div class="loan-icon"><i class="ph ph-circuit-board"></i></div>
          <div class="loan-details">
            <div class="loan-name">${loans[id].name}</div>
            <div class="loan-meta">
              <span><i class="ph ph-hash"></i> ${loans[id].tag}</span>
              <span><i class="ph ph-map-pin"></i> ${loans[id].location}</span>
              <span><i class="ph ph-calendar-check"></i> Due: ${dueStr}</span>
            </div>
          </div>
          <span class="loan-status status-${statusClass}">${statusLabel}</span>
          <div class="loan-select-indicator"><i class="ph-fill ph-check"></i></div>
        `;
        listEl.appendChild(card);
      });
    } catch (e) {
      console.error('Failed loading loans:', e);
      document.getElementById('loansList').innerHTML = `
        <div style="padding: 20px; text-align: center; color: var(--danger);">Failed to load your loans. Please refresh.</div>
      `;
    }
  }

  // Load loans on startup
  document.addEventListener('DOMContentLoaded', () => {
    if (window.api && window.api.isAuthenticated()) {
      fetchNotifications();
      setInterval(fetchNotifications, 15000);
      loadActiveLoans();
    } else {
      window.location.href = 'login.php';
    }
  });

  /* ── SELECT LOAN ── */
  function selectLoan(id) {
    document.querySelectorAll('.loan-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('loan-' + id).classList.add('selected');
    selectedLoan = id;
    document.getElementById('proceedBtn').disabled = false;
  }

  /* ── SELECT CONDITION ── */
  function selectCondition(val) {
    selectedCondition = val;
    ['good', 'fair', 'damaged', 'lost'].forEach(k => {
      document.getElementById('cond-' + k).classList.remove('selected');
    });
    document.getElementById('cond-' + val.toLowerCase()).classList.add('selected');
  }

  /* ── STEPPER ── */
  function goToStep(n) {
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    document.getElementById('step' + n).classList.add('active');

    [1, 2, 3].forEach(i => {
      const item = document.getElementById('stepItem' + i);
      item.classList.remove('active', 'done');
      if (i < n)  item.classList.add('done');
      if (i === n) item.classList.add('active');
    });
    [1, 2].forEach(i => {
      const line = document.getElementById('line' + i);
      line.classList.toggle('done', i < n);
    });

    if (n === 2) populateStep2();
  }

  /* ── POPULATE STEP 2 ── */
  function populateStep2() {
    const loan = loans[selectedLoan];

    document.getElementById('r-name').textContent     = loan.name;
    document.getElementById('r-tag').textContent      = loan.tag;
    document.getElementById('r-location').textContent = loan.location;
    document.getElementById('r-due').textContent      = loan.due;

    const statusEl = document.getElementById('r-status');
    statusEl.textContent = loan.statusLabel;
    statusEl.className   = 'info-value ' + loan.statusClass;

    // Auto-fill current time
    const now = new Date();
    document.getElementById('returnDateTime').textContent = now.toLocaleString('en-PH', {
      month: 'long', day: 'numeric', year: 'numeric',
      hour: '2-digit', minute: '2-digit'
    });

    // Overdue banner
    const banner = document.getElementById('overdueBanner');
    banner.classList.toggle('hidden', !loan.isOverdue);

    // Reset condition & terms
    selectedCondition = null;
    ['good', 'fair', 'damaged', 'lost'].forEach(k => {
      document.getElementById('cond-' + k).classList.remove('selected');
    });
    document.getElementById('terms').checked = false;
    document.getElementById('returnNotes').value = '';
  }

  /* ── RETURN PHOTOS STATE ── */
  let returnPhotoFiles = [];

  function previewReturnPhotos(input) {
    const newFiles = Array.from(input.files);
    if (returnPhotoFiles.length + newFiles.length > 5) {
      alert('Maximum 5 photos allowed.');
      return;
    }
    newFiles.forEach(f => {
      returnPhotoFiles.push(f);
      const reader = new FileReader();
      reader.onload = function(e) {
        const idx = returnPhotoFiles.indexOf(f);
        const container = document.getElementById('returnPhotoPreviews');
        const item = document.createElement('div');
        item.className = 'photo-preview-item';
        item.dataset.idx = idx;
        item.innerHTML = `
          <img src="${e.target.result}" alt="Return photo">
          <button class="remove-photo" onclick="removeReturnPhoto(${idx}, this)" type="button"><i class="ph ph-x"></i></button>
        `;
        container.appendChild(item);
      };
      reader.readAsDataURL(f);
    });
    input.value = '';
  }

  function removeReturnPhoto(idx, btn) {
    returnPhotoFiles[idx] = null;
    btn.closest('.photo-preview-item').remove();
  }

  /* ── SUBMIT ── */
  async function handleSubmit() {
    if (!selectedCondition) {
      alert('Please select the asset condition before submitting.');
      return;
    }
    if (!document.getElementById('terms').checked) {
      alert('Please acknowledge the return confirmation before submitting.');
      return;
    }

    const loan  = loans[selectedLoan];

    if (loan.isOverdue) {
      const validPhotos = returnPhotoFiles.filter(f => f !== null);
      if (validPhotos.length === 0) {
        alert('Photo proof is required for returning overdue assets (e.g., payment proofs & item condition). Please upload at least one photo.');
        return;
      }
    }

    const notes = document.getElementById('returnNotes').value.trim() || 'No additional notes provided.';
    const now   = new Date().toLocaleString('en-PH', {
      month: 'long', day: 'numeric', year: 'numeric',
      hour: '2-digit', minute: '2-digit'
    });

    try {
      // 1. Call the JWT-authenticated return API
      const res = await window.api.authenticatedFetch('/transactions/return.php', {
        method: 'PUT',
        body: { transaction_id: selectedLoan }
      });

      // 2. Upload return photos if any
      const validPhotos = returnPhotoFiles.filter(f => f !== null);
      let uploadedUrls = [];
      if (validPhotos.length > 0) {
        const formData = new FormData();
        formData.append('transaction_id', selectedLoan);
        validPhotos.forEach(f => formData.append('return_photos[]', f));

        const token = window.api.getToken();
        try {
          const uploadRes = await fetch('/SD_FINALPROJECT_GRP6/HariBorrow_backend/api/transactions/upload_return_photos.php', {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${token}` },
            body: formData
          }).then(r => r.json());
          if (uploadRes?.photos) {
            uploadedUrls = uploadRes.photos;
          }
        } catch (photoErr) {
          console.warn('Photo upload failed:', photoErr);
        }
      }

      // 3. Show success summary
      document.getElementById('s-name').textContent       = loan.name;
      document.getElementById('s-tag').textContent        = loan.tag;
      document.getElementById('s-location').textContent   = loan.location;
      document.getElementById('s-datetime').textContent   = now;
      document.getElementById('s-condition').textContent  = selectedCondition;
      document.getElementById('s-loan-status').textContent = 'Pending Admin Review';
      document.getElementById('s-loan-status').style.color = 'var(--gold)';
      document.getElementById('s-notes').textContent      = notes;

      // Show penalty estimate if any
      if (res?.penalty_amount && Number(res.penalty_amount) > 0) {
        document.getElementById('s-loan-status').textContent += ` — Est. Penalty: PHP ${Number(res.penalty_amount).toLocaleString()}`;
        document.getElementById('s-loan-status').style.color = 'var(--danger)';
      }

      // Show uploaded photos in summary
      const photosSection = document.getElementById('s-photos-section');
      const photosContainer = document.getElementById('s-photos');
      if (uploadedUrls.length > 0) {
        photosSection.style.display = 'block';
        photosContainer.innerHTML = uploadedUrls.map(url => `<img src="${url}" alt="Return photo">`).join('');
      }

      goToStep(3);
      returnPhotoFiles = [];

      // Show toast
      showReturnToast('Return Submitted Successfully', 'Your return is pending admin review. You\'ll be notified once approved.');
    } catch (error) {
      console.error('Error:', error);
      alert('Error submitting return: ' + (error?.message || 'Unknown error'));
    }
  }

  function showReturnToast(title, msg) {
    const toast = document.getElementById('returnToast');
    document.getElementById('toastTitle').textContent = title;
    document.getElementById('toastMsg').textContent = msg;
    toast.style.display = 'flex';
    toast.style.animation = 'none';
    toast.offsetHeight; // force reflow
    toast.style.animation = 'fadeIn 0.4s ease';
    setTimeout(() => { toast.style.display = 'none'; }, 5000);
  }
</script>
</body>
</html>