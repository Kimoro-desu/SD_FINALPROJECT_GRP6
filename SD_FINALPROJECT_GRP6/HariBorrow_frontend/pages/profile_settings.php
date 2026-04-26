<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HariBorrow — Profile Settings</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600&family=Pinyon+Script&display=swap"
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
    }

    html,
    body {
      height: 100vh;
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
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      z-index: 0;
      background:
        radial-gradient(circle at 20% 20%, rgba(229, 192, 123, 0.08), transparent 40%),
        radial-gradient(circle at 80% 80%, rgba(166, 138, 72, 0.05), transparent 50%),
        var(--bg-deep);
      animation: pulseBg 20s ease-in-out infinite alternate;
    }

    @keyframes pulseBg {
      0% {
        transform: scale(1);
      }

      100% {
        transform: scale(1.03);
      }
    }

    .ambient-glow {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      pointer-events: none;
      background: radial-gradient(600px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(229, 192, 123, 0.06), transparent 50%);
      z-index: 9999;
      mix-blend-mode: screen;
      transition: background 0.1s;
    }

    /* ── TOP NAVIGATION ── */
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

    .back-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      background: transparent;
      border: 1px solid var(--glass-border);
      color: var(--text-2);
      padding: 8px 16px;
      border-radius: 30px;
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      text-decoration: none;
      transition: all 0.3s;
    }

    .back-btn:hover {
      border-color: var(--gold);
      color: var(--gold);
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

    /* ── MAIN LAYOUT ── */
    .settings-container {
      position: relative;
      z-index: 10;
      padding: 120px 5% 60px;
      max-width: 1200px;
      margin: 0 auto;
      animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .page-header {
      margin-bottom: 40px;
    }

    .page-header h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 48px;
      font-weight: 400;
      color: var(--text-1);
      line-height: 1.1;
    }

    .page-header .aesthetic-script {
      font-family: 'Pinyon Script', cursive;
      font-size: 1.3em;
      color: var(--gold-light);
      text-shadow: 0 0 20px rgba(229, 192, 123, 0.4);
    }

    .page-header p {
      font-size: 14px;
      color: var(--text-2);
      font-weight: 300;
      margin-top: 8px;
      letter-spacing: 0.03em;
    }

    .settings-grid {
      display: grid;
      grid-template-columns: 260px 1fr;
      gap: 40px;
    }

    /* Sidebar Navigation */
    .settings-nav {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .tab-btn {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 16px;
      background: transparent;
      border: 1px solid transparent;
      border-radius: 12px;
      color: var(--text-2);
      font-family: 'Outfit', sans-serif;
      font-size: 14px;
      font-weight: 400;
      text-align: left;
      cursor: pointer;
      transition: all 0.3s;
    }

    .tab-btn i {
      font-size: 20px;
      color: var(--text-3);
      transition: color 0.3s;
    }

    .tab-btn:hover {
      background: rgba(255, 255, 255, 0.02);
      color: var(--text-1);
    }

    .tab-btn.active {
      background: var(--glass);
      border-color: var(--glass-border);
      color: var(--gold-light);
      box-shadow: inset 2px 0 0 var(--gold);
    }

    .tab-btn.active i {
      color: var(--gold);
    }

    /* Panels */
    .settings-panel {
      background: var(--glass);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border: 1px solid var(--glass-border);
      border-radius: 16px;
      padding: 40px;
      display: none;
      animation: fadeUp 0.4s ease both;
    }

    .settings-panel.active {
      display: block;
    }

    .panel-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 28px;
      font-weight: 500;
      color: var(--text-1);
      margin-bottom: 8px;
    }

    .panel-desc {
      font-size: 13px;
      color: var(--text-3);
      font-weight: 300;
      margin-bottom: 32px;
      padding-bottom: 24px;
      border-bottom: 1px solid var(--glass-border);
    }

    /* Forms */
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin-bottom: 24px;
    }

    .field {
      position: relative;
    }

    label {
      display: block;
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: var(--text-2);
      margin-bottom: 10px;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    select {
      width: 100%;
      padding: 14px 16px;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid var(--glass-border);
      border-radius: 8px;
      color: var(--text-1);
      font-family: 'Outfit', sans-serif;
      font-size: 14px;
      font-weight: 300;
      outline: none;
      transition: all 0.3s;
      appearance: none;
    }

    input:focus,
    select:focus {
      background: rgba(0, 0, 0, 0.4);
      border-color: var(--gold);
      box-shadow: 0 0 15px rgba(229, 192, 123, 0.15);
    }

    /* Avatar Upload */
    .avatar-upload {
      display: flex;
      align-items: center;
      gap: 24px;
      margin-bottom: 40px;
    }

    .avatar-preview {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: var(--gold-dark);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 32px;
      color: var(--bg-deep);
      font-family: 'Cormorant Garamond', serif;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
      overflow: hidden;
    }

    .avatar-preview img,
    .profile-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 50%;
    }

    .avatar-actions {
      display: flex;
      gap: 12px;
    }

    .btn-small {
      background: transparent;
      border: 1px solid var(--glass-border);
      color: var(--text-2);
      padding: 8px 16px;
      border-radius: 6px;
      font-size: 12px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-small:hover {
      border-color: var(--gold);
      color: var(--gold);
    }

    /* Verification Box */
    .verification-box {
      background: rgba(255, 255, 255, 0.02);
      border: 1px dashed var(--glass-border);
      border-radius: 8px;
      padding: 20px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 32px;
    }

    .verify-text h4 {
      font-family: 'Outfit', sans-serif;
      font-size: 15px;
      font-weight: 500;
      color: var(--text-1);
      margin-bottom: 6px;
    }

    .verify-text p {
      font-size: 12px;
      color: var(--text-3);
      margin-bottom: 12px;
      max-width: 400px;
      line-height: 1.5;
    }

    .status-badge {
      font-size: 10px;
      font-weight: 600;
      padding: 4px 10px;
      border-radius: 4px;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      display: inline-block;
    }

    .status-badge.unverified {
      background: rgba(220, 53, 69, 0.1);
      color: #ff6b7a;
      border: 1px solid rgba(220, 53, 69, 0.2);
    }

    .status-badge.pending {
      background: rgba(229, 192, 123, 0.1);
      color: var(--gold);
      border: 1px solid rgba(229, 192, 123, 0.2);
    }

    /* Role Toggle Pill */
    .role-toggle {
      display: inline-flex;
      background: rgba(0, 0, 0, 0.4);
      border: 1px solid var(--glass-border);
      border-radius: 40px;
      padding: 6px;
      position: relative;
      margin-bottom: 32px;
    }

    .role-btn {
      flex: 1;
      min-width: 120px;
      background: transparent;
      border: none;
      padding: 10px 24px;
      border-radius: 30px;
      color: var(--text-2);
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      font-weight: 500;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      cursor: pointer;
      transition: all 0.3s;
      position: relative;
      z-index: 2;
    }

    .role-btn.active {
      color: var(--bg-deep);
      font-weight: 600;
    }

    .role-indicator {
      position: absolute;
      top: 6px;
      bottom: 6px;
      left: 6px;
      width: calc(50% - 6px);
      background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
      border-radius: 30px;
      z-index: 1;
      transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
      box-shadow: 0 4px 15px var(--gold-glow);
    }

    .role-toggle[data-mode="lender"] .role-indicator {
      transform: translateX(100%);
    }

    /* Conditional Role Fields */
    .role-settings {
      display: none;
      animation: fadeUp 0.4s ease;
    }

    .role-settings.active {
      display: block;
    }

    .section-subtitle {
      font-size: 14px;
      font-weight: 500;
      color: var(--gold);
      margin: 32px 0 16px;
      border-bottom: 1px solid var(--glass-border);
      padding-bottom: 8px;
      font-family: 'Cormorant Garamond', serif;
      letter-spacing: 0.05em;
    }

    /* Toggle Switch */
    .switch-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 0;
      border-bottom: 1px solid var(--glass-border);
    }

    .switch-label {
      font-size: 14px;
      color: var(--text-1);
      font-weight: 400;
    }

    .switch-desc {
      font-size: 12px;
      color: var(--text-3);
      margin-top: 4px;
    }

    .switch {
      position: relative;
      display: inline-block;
      width: 44px;
      height: 24px;
    }

    .switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(255, 255, 255, 0.1);
      transition: .4s;
      border-radius: 34px;
      border: 1px solid var(--glass-border);
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 16px;
      width: 16px;
      left: 3px;
      bottom: 3px;
      background-color: var(--text-2);
      transition: .4s;
      border-radius: 50%;
    }

    input:checked+.slider {
      background-color: var(--gold-dark);
      border-color: var(--gold);
    }

    input:checked+.slider:before {
      transform: translateX(20px);
      background-color: var(--bg-deep);
    }

    .save-btn {
      padding: 16px 32px;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid var(--gold);
      border-radius: 8px;
      color: var(--gold);
      font-family: 'Outfit', sans-serif;
      font-size: 13px;
      font-weight: 500;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      cursor: pointer;
      transition: all 0.4s;
      position: relative;
      overflow: hidden;
      margin-top: 32px;
      float: right;
    }

    .save-btn::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(90deg, transparent, rgba(229, 192, 123, 0.4), transparent);
      transform: translateX(-100%);
      transition: transform 0.6s ease;
    }

    .save-btn span {
      position: relative;
      z-index: 1;
      transition: color 0.3s;
    }

    .save-btn:hover {
      background: var(--gold);
      border-color: var(--gold);
      box-shadow: 0 10px 30px rgba(229, 192, 123, 0.3);
    }

    .save-btn:hover span {
      color: var(--bg-deep);
      font-weight: 600;
    }

    .save-btn:hover::before {
      transform: translateX(100%);
    }

    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (max-width: 900px) {
      .settings-grid {
        grid-template-columns: 1fr;
        gap: 24px;
      }

      .settings-nav {
        flex-direction: row;
        overflow-x: auto;
        padding-bottom: 8px;
      }

      .tab-btn {
        white-space: nowrap;
        padding: 12px 16px;
      }

      .form-row {
        grid-template-columns: 1fr;
        gap: 16px;
      }

      .verification-box {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
      }
    }
  </style>
</head>

<body>

  <div class="bg-mesh"></div>
  <div class="ambient-glow" id="glow"></div>

  <nav class="top-nav">
    <a href="#" class="nav-brand">
      <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="HariBorrow Logo" class="nav-logo">
      <span class="nav-title">HariBorrow</span>
    </a>
    <div style="display: flex; align-items: center; gap: 16px;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <div class="profile-avatar" id="navAvatar">--</div>
        <span style="font-size: 14px; font-weight: 500;" id="navUserName">Loading...</span>
      </div>
      <a href="borrower_lender_dashboard.php" class="back-btn">
        <i class="ph ph-arrow-left"></i> Dashboard
      </a>
    </div>
  </nav>

  <main class="settings-container">
    <div class="page-header">
      <h1>Account <span class="aesthetic-script">Settings.</span></h1>
      <p>Manage your personal information, security preferences, and system roles.</p>
    </div>

    <div class="settings-grid">
      <nav class="settings-nav">
        <button class="tab-btn active" onclick="switchTab('profileTab', this)">
          <i class="ph ph-user-circle"></i> Personal Information
        </button>
        <button class="tab-btn" onclick="switchTab('roleTab', this)">
          <i class="ph ph-briefcase"></i> Role Configuration
        </button>
        <button class="tab-btn" onclick="switchTab('securityTab', this)">
          <i class="ph ph-lock-key"></i> Security & Authentication
        </button>
        <button class="tab-btn" style="color: #ff6b7a; margin-top: auto;"
          onclick="window.api.removeToken(); window.location.href='login.php'">
          <i class="ph ph-sign-out" style="color: #ff6b7a;"></i> Secure Log Out
        </button>
      </nav>

      <div class="content-area">

        <div id="profileTab" class="settings-panel active">
          <h2 class="panel-title">Personal Information</h2>
          <p class="panel-desc">Update your official university directory details and verification status.</p>

          <div class="avatar-upload">
            <div class="avatar-preview" id="mainAvatar">--</div>
            <div class="avatar-actions">
              <input type="file" id="avatarInput" accept="image/*" style="display: none;"
                onchange="previewAvatar(event)">
              <button class="btn-small" onclick="document.getElementById('avatarInput').click()">
                <i class="ph ph-upload-simple"></i> Upload New
              </button>
              <button class="btn-small" style="color: var(--text-3); border-color: transparent;"
                onclick="removeAvatar()">Remove</button>
            </div>
          </div>

          <div class="form-row">
            <div class="field">
              <label>Full Legal Name</label>
              <input type="text" id="legalNameInput" placeholder="Loading...">
            </div>
            <div class="field">
              <label>Student / Employee ID</label>
              <input type="text" id="studentIdInput" disabled style="opacity: 0.6; cursor: not-allowed;"
                placeholder="Not available in database yet">
            </div>
          </div>

          <div class="verification-box">
            <div class="verify-text">
              <h4>Identity Verification</h4>
              <p>Upload a clear photo of your valid Pamantasan School ID to verify your account and unlock borrowing
                privileges.</p>
              <span class="status-badge unverified" id="verifyBadge">Status: Unverified</span>
            </div>
            <div class="verify-action">
              <input type="file" id="idInput" accept="image/*" style="display: none;" onchange="handleIdUpload(event)">
              <button class="btn-small" id="verifyBtn" onclick="document.getElementById('idInput').click()">
                <i class="ph ph-identification-card"></i> Upload ID
              </button>
            </div>
          </div>

          <div class="form-row">
            <div class="field">
              <label>University Email</label>
              <input type="email" id="emailInput" placeholder="Loading...">
            </div>
            <div class="field">
              <label>Primary College / Department</label>
              <input type="text" id="departmentInput" disabled style="opacity: 0.6; cursor: not-allowed;"
                placeholder="Not available in database yet">
            </div>
          </div>

          <button class="save-btn" onclick="saveFeedback(this)"><span>Save Changes</span></button>
          <div style="clear: both;"></div>
        </div>

        <div id="roleTab" class="settings-panel">
          <h2 class="panel-title">Role Configuration</h2>
          <p class="panel-desc">Customize your system behavior based on your current operational role.</p>

          <div class="role-toggle" id="settingsRoleToggle" data-mode="borrower">
            <div class="role-indicator"></div>
            <button class="role-btn active" onclick="switchRoleSettings('borrower')">Borrower Settings</button>
            <button class="role-btn" onclick="switchRoleSettings('lender')">Lender Settings</button>
          </div>

          <div id="borrowerConfig" class="role-settings active">
            <div class="switch-row">
              <div>
                <div class="switch-label">Due Date Reminders</div>
                <div class="switch-desc">Receive email alerts 12 hours before an asset is due for return.</div>
              </div>
              <label class="switch"><input type="checkbox" checked><span class="slider"></span></label>
            </div>
            <div class="switch-row">
              <div>
                <div class="switch-label">Auto-Renew Requests</div>
                <div class="switch-desc">Automatically request a 24-hour extension if the asset is not reserved.</div>
              </div>
              <label class="switch"><input type="checkbox"><span class="slider"></span></label>
            </div>

            <h3 class="section-subtitle">Default Academic Details</h3>

            <div class="form-row">
              <div class="field">
                <label>Supervising Professor (Optional)</label>
                <input type="text" placeholder="e.g. Engr. Dela Cruz">
              </div>
              <div class="field">
                <label>Borrowing For Department</label>
                <select>
                  <option>Select Department...</option>
                  <option>Computer Engineering</option>
                  <option>Civil Engineering</option>
                  <option>Information Technology</option>
                  <option>College of Science</option>
                  <option>University Library</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="field">
                <label>Default Pickup Location</label>
                <select>
                  <option>Library</option>
                  <option>University Activity Center</option>
                  <option>Tanghalang Bayan</option>
                </select>
              </div>
            </div>
          </div>

          <div id="lenderConfig" class="role-settings">
            <div class="switch-row">
              <div>
                <div class="switch-label">Instant Approvals</div>
                <div class="switch-desc">Automatically approve borrow requests from Faculty accounts.</div>
              </div>
              <label class="switch"><input type="checkbox"><span class="slider"></span></label>
            </div>
            <div class="switch-row">
              <div>
                <div class="switch-label">Overdue Alerts</div>
                <div class="switch-desc">Send automated warning emails to users who miss their return window.</div>
              </div>
              <label class="switch"><input type="checkbox" checked><span class="slider"></span></label>
            </div>
            <div class="form-row" style="margin-top: 24px;">
              <div class="field">
                <label>Managed Asset Category</label>
                <select>
                  <option>Engineering & Technical Tools</option>
                  <option>Audio-Visual & Presentation</option>
                  <option>Laboratory Equipment</option>
                </select>
              </div>
            </div>
          </div>

          <button class="save-btn" onclick="saveFeedback(this)"><span>Save Preferences</span></button>
          <div style="clear: both;"></div>
        </div>

        <div id="securityTab" class="settings-panel">
          <h2 class="panel-title">Security & Authentication</h2>
          <p class="panel-desc">Manage your password and secure your gateway access.</p>

          <div class="form-row">
            <div class="field">
              <label>Current Password</label>
              <input type="password" placeholder="••••••••">
            </div>
          </div>
          <div class="form-row">
            <div class="field">
              <label>New Password</label>
              <input type="password" placeholder="Minimum 8 characters">
            </div>
            <div class="field">
              <label>Confirm New Password</label>
              <input type="password" placeholder="Must match new password">
            </div>
          </div>

          <button class="save-btn" onclick="saveFeedback(this)"><span>Update Security</span></button>
          <div style="clear: both;"></div>
        </div>

      </div>
    </div>
  </main>

  <script>
    const glow = document.getElementById('glow');
    document.addEventListener('mousemove', (e) => {
      glow.style.setProperty('--mouse-x', e.clientX + 'px');
      glow.style.setProperty('--mouse-y', e.clientY + 'px');
    });

    // Load Dynamic Data when Page Loads
    document.addEventListener('DOMContentLoaded', () => {
      loadUserProfile();
    });

    // Main Database Fetch Function
    async function loadUserProfile() {
      try {
        // Because getting profile info requires being logged in, we use authenticatedFetch!
        // Make sure this path matches where your profile.php actually lives
        const response = await window.api.authenticatedFetch('/api/users/profile.php', {
          method: 'GET'
        });

        if (response && response.status === 'success') {
          const profile = response.profile;

          // 1. Populate Navigation Name
          document.getElementById('navUserName').textContent = profile.full_name || profile.first_name;

          // 2. Generate and Set Initials (e.g. Juan Mirasol = JM)
          const firstInitial = profile.first_name ? profile.first_name.charAt(0) : '';
          const lastInitial = profile.last_name ? profile.last_name.charAt(0) : '';
          const initials = (firstInitial + lastInitial).toUpperCase();

          window.currentUserInitials = initials; // Store to reuse if avatar is removed
          document.getElementById('navAvatar').textContent = initials;
          document.getElementById('mainAvatar').textContent = initials;

          // 3. Populate Input Fields
          document.getElementById('legalNameInput').value = profile.full_name;
          document.getElementById('emailInput').value = profile.email;

          // Note: If you update profile.php to SELECT school_id_number, it will automatically show up here!
          if (profile.school_id_number) {
            document.getElementById('studentIdInput').value = profile.school_id_number;
          }
          if (profile.department) {
            document.getElementById('departmentInput').value = profile.department;
          }

        }
      } catch (error) {
        console.error("Failed to load profile:", error);
        // Note: auth_guard will automatically handle redirecting if the token is completely invalid.
      }
    }


    function switchTab(tabId, btn) {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById(tabId).classList.add('active');
    }

    function switchRoleSettings(role) {
      const toggleContainer = document.getElementById('settingsRoleToggle');
      toggleContainer.setAttribute('data-mode', role);
      const btns = toggleContainer.querySelectorAll('.role-btn');
      btns.forEach(btn => btn.classList.remove('active'));
      event.target.classList.add('active');
      document.querySelectorAll('.role-settings').forEach(rs => rs.classList.remove('active'));
      document.getElementById(role + 'Config').classList.add('active');
    }

    function saveFeedback(btn) {
      const textSpan = btn.querySelector('span');
      const originalText = textSpan.textContent;
      textSpan.textContent = 'Saving...';
      btn.disabled = true;

      setTimeout(() => {
        textSpan.textContent = '✓ Saved Successfully';
        btn.style.borderColor = '#4ade80';
        btn.style.color = '#4ade80';

        setTimeout(() => {
          textSpan.textContent = originalText;
          btn.disabled = false;
          btn.style.borderColor = 'var(--gold)';
          btn.style.color = 'var(--gold)';
        }, 2000);
      }, 800);
    }

    // Live Avatar Preview Logic
    function previewAvatar(event) {
      if (event.target.files && event.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
          const imgTag = `<img src="${e.target.result}" alt="Profile Picture">`;
          document.getElementById('mainAvatar').innerHTML = imgTag;
          document.getElementById('navAvatar').innerHTML = imgTag;
        }
        reader.readAsDataURL(event.target.files[0]);
      }
    }

    function removeAvatar() {
      // Reverts back to the dynamic initials we generated on page load!
      const initials = window.currentUserInitials || "UN";
      document.getElementById('mainAvatar').innerHTML = initials;
      document.getElementById('navAvatar').innerHTML = initials;
      document.getElementById('avatarInput').value = ""; // Reset file input
    }

    // School ID Upload Logic
    function handleIdUpload(event) {
      if (event.target.files && event.target.files[0]) {
        const badge = document.getElementById('verifyBadge');
        const btn = document.getElementById('verifyBtn');

        badge.className = 'status-badge pending';
        badge.textContent = 'Status: Pending Verification';

        btn.innerHTML = '<i class="ph ph-check-circle"></i> ID Submitted';
        btn.style.color = '#4ade80';
        btn.style.borderColor = '#4ade80';
      }
    }
  </script>
</body>

</html>