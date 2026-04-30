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
      background: radial-gradient(480px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(229, 192, 123, 0.1), rgba(229, 192, 123, 0.03) 38%, transparent 68%);
      z-index: 9999;
      mix-blend-mode: screen;
      transition: background 0.08s ease-out;
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
      padding: 140px 5% 60px;
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
          </div>

          <div class="form-row">
            <div class="field">
              <label>Full Legal Name</label>
              <input type="text" id="legalNameInput" disabled style="opacity: 0.6; cursor: not-allowed;" placeholder="Loading...">
            </div>
            <div class="field">
              <label>Student / Employee ID</label>
              <input type="text" id="studentIdInput" disabled style="opacity: 0.6; cursor: not-allowed;"
                placeholder="Loading...">
            </div>
          </div>

          <div class="verification-box">
            <div class="verify-text">
              <h4>Identity Verification</h4>
              <p>Upload a clear photo of your valid Pamantasan School ID to verify your account and unlock borrowing
                privileges.</p>
              <span class="status-badge unverified" id="verifyBadge">Status: Unverified</span>
            </div>
            <div class="verify-action" style="display: flex; flex-direction: column; gap: 12px; align-items: flex-end;">
              <div id="idPreviewContainer" style="display: none; width: 100%; max-width: 200px; border: 1px solid var(--glass-border); border-radius: 8px; overflow: hidden;">
                <img id="idPreviewImage" src="" alt="ID Preview" style="width: 100%; display: block; object-fit: cover;">
              </div>
              
              <div style="display: flex; gap: 8px;">
                <input type="file" id="idInput" accept="image/*" style="display: none;" onchange="previewIdUpload(event)">
                <button class="btn-small" id="verifyBtn" onclick="document.getElementById('idInput').click()">
                  <i class="ph ph-identification-card"></i> Upload ID
                </button>
                <button class="btn-small" id="submitIdBtn" style="display: none; color: var(--gold); border-color: var(--gold);" onclick="submitIdUpload()">
                  <i class="ph ph-upload-simple"></i> Submit ID
                </button>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="field">
              <label>University Email</label>
              <input type="email" id="emailInput" disabled style="opacity: 0.6; cursor: not-allowed;" placeholder="Loading...">
            </div>
            <div class="field">
              <label>Primary College / Department</label>
              <input type="text" id="departmentInput" disabled style="opacity: 0.6; cursor: not-allowed;"
                placeholder="Loading...">
            </div>
          </div>

          <div style="clear: both;"></div>
        </div>

        <div id="securityTab" class="settings-panel">
          <h2 class="panel-title">Security & Authentication</h2>
          <p class="panel-desc">Manage your password and secure your gateway access.</p>

          <div class="form-row">
            <div class="field">
              <label>Current Password</label>
              <input type="password" id="currentPasswordInput" placeholder="••••••••">
            </div>
          </div>
          <div class="form-row">
            <div class="field">
              <label>New Password</label>
              <input type="password" id="newPasswordInput" placeholder="Minimum 8 characters">
            </div>
            <div class="field">
              <label>Confirm New Password</label>
              <input type="password" id="confirmPasswordInput" placeholder="Must match new password">
            </div>
          </div>

          <button type="button" class="save-btn" onclick="saveFeedback(this, 'security')"><span>Update Security</span></button>
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
        const response = await window.api.authenticatedFetch('/api/users/profile.php', {
          method: 'GET'
        });

        if (response && response.status === 'success') {
          const profile = response.profile;

          // 1. Populate Navigation Name
          document.getElementById('navUserName').textContent = profile.full_name || profile.first_name;

          // 2. Generate and Set Initials
          const firstInitial = profile.first_name ? profile.first_name.charAt(0) : '';
          const lastInitial = profile.last_name ? profile.last_name.charAt(0) : '';
          const initials = (firstInitial + lastInitial).toUpperCase();

          window.currentUserInitials = initials; 
          
          if (profile.profile_picture) {
            const imgTag = `<img src="${profile.profile_picture}" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
            document.getElementById('navAvatar').innerHTML = imgTag;
            document.getElementById('mainAvatar').innerHTML = imgTag;
          } else {
            document.getElementById('navAvatar').textContent = initials;
            document.getElementById('mainAvatar').textContent = initials;
          }

          // 3. Populate Input Fields
          document.getElementById('legalNameInput').value = profile.full_name;
          document.getElementById('emailInput').value = profile.email;

          if (profile.school_id_number) {
            document.getElementById('studentIdInput').value = profile.school_id_number;
          }
          if (profile.department) {
            document.getElementById('departmentInput').value = profile.department;
          }
          
          // 4. Verification Status
          const verifyBadge = document.getElementById('verifyBadge');
          const verifyBtn = document.getElementById('verifyBtn');
          const vStatus = profile.id_verification_status || 'unverified';
          
          if (vStatus === 'verified') {
            verifyBadge.className = 'status-badge';
            verifyBadge.style.backgroundColor = 'rgba(74, 222, 128, 0.1)';
            verifyBadge.style.color = 'var(--green)';
            verifyBadge.style.borderColor = 'rgba(74, 222, 128, 0.2)';
            verifyBadge.textContent = 'Status: Verified';
            verifyBtn.innerHTML = '<i class="ph ph-check-circle"></i> Verified';
            verifyBtn.style.color = '#4ade80';
            verifyBtn.style.borderColor = '#4ade80';
            verifyBtn.disabled = true;
          } else if (vStatus === 'pending') {
            verifyBadge.className = 'status-badge pending';
            verifyBadge.textContent = 'Status: Pending Verification';
            verifyBtn.innerHTML = '<i class="ph ph-hourglass"></i> Pending Approval';
            verifyBtn.disabled = true;
          } else {
            verifyBadge.className = 'status-badge unverified';
            verifyBadge.textContent = 'Status: Unverified';
            verifyBtn.innerHTML = '<i class="ph ph-identification-card"></i> Upload ID';
            verifyBtn.disabled = false;
          }

        }
      } catch (error) {
        console.error("Failed to load profile:", error);
      }
    }

    function switchTab(tabId, btn) {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById(tabId).classList.add('active');
    }

    async function saveFeedback(btn, action) {
      const textSpan = btn.querySelector('span');
      const originalText = textSpan.textContent;
      textSpan.textContent = 'Saving...';
      btn.disabled = true;

      try {
        let payload = null;
        if (action === 'security') {
          const currentPass = document.getElementById('currentPasswordInput').value;
          const newPass = document.getElementById('newPasswordInput').value;
          const confirmPass = document.getElementById('confirmPasswordInput').value;
          
          // 1. Check if any fields are empty
          if (!currentPass || !newPass || !confirmPass) {
            alert("Please fill in all password fields.");
            textSpan.textContent = originalText;
            btn.disabled = false;
            return;
          }

          // 2. Check minimum length
          if (newPass.length < 8) {
            alert("New password must be at least 8 characters long.");
            textSpan.textContent = originalText;
            btn.disabled = false;
            return;
          }

          // 3. Check if new passwords match
          if (newPass !== confirmPass) {
            alert("New passwords do not match.");
            textSpan.textContent = originalText;
            btn.disabled = false;
            return;
          }
          
          // Create the payload exactly as update_profile.php expects it
          payload = {
            action: 'password',
            current_password: currentPass,
            new_password: newPass
          };
        }

        if (payload) {
          const res = await window.api.authenticatedFetch('/api/users/update_profile.php', {
            method: 'PUT',
            body: payload
          });
          
          // If update_profile.php returns an error (e.g. incorrect current password), throw it
          if (res && res.status === 'error') throw new Error(res.message);
        }

        // Success UI updates
        textSpan.textContent = '✓ Saved Successfully';
        btn.style.borderColor = '#4ade80';
        btn.style.color = '#4ade80';

        // Clear the fields after a successful update
        if (action === 'security') {
            document.getElementById('currentPasswordInput').value = '';
            document.getElementById('newPasswordInput').value = '';
            document.getElementById('confirmPasswordInput').value = '';
        }

        setTimeout(() => {
          textSpan.textContent = originalText;
          btn.disabled = false;
          btn.style.borderColor = 'var(--gold)';
          btn.style.color = 'var(--gold)';
        }, 2000);
        
      } catch (e) {
        alert(e.message || "Failed to save changes.");
        textSpan.textContent = originalText;
        btn.disabled = false;
      }
    }

    // School ID Upload Logic
    // School ID Upload Logic
    let selectedIdFile = null;

    // 1. Preview the image first
    function previewIdUpload(event) {
      if (event.target.files && event.target.files[0]) {
        selectedIdFile = event.target.files[0];
        
        const reader = new FileReader();
        reader.onload = function(e) {
          // Show the image preview
          document.getElementById('idPreviewImage').src = e.target.result;
          document.getElementById('idPreviewContainer').style.display = 'block';
          
          // Change the "Upload" button to "Change ID"
          document.getElementById('verifyBtn').innerHTML = '<i class="ph ph-arrows-clockwise"></i> Change ID';
          
          // Reveal the Submit button
          document.getElementById('submitIdBtn').style.display = 'inline-flex';
          document.getElementById('submitIdBtn').style.alignItems = 'center';
          document.getElementById('submitIdBtn').style.gap = '6px';
        };
        reader.readAsDataURL(selectedIdFile);
      }
    }

    // 2. Manually submit the image to the backend
    async function submitIdUpload() {
      if (!selectedIdFile) {
          alert("Please select an ID image first.");
          return;
      }

      const submitBtn = document.getElementById('submitIdBtn');
      const verifyBtn = document.getElementById('verifyBtn');
      const originalText = submitBtn.innerHTML;
      
      submitBtn.innerHTML = 'Submitting...';
      submitBtn.disabled = true;
      verifyBtn.disabled = true; // Prevent changing file while uploading
      
      try {
          const formData = new FormData();
          formData.append('id_picture', selectedIdFile);
          
          const token = window.api.getToken();
          const res = await fetch('/SD_FINALPROJECT_GRP6/HariBorrow_backend/api/users/upload_pictures.php', {
              method: 'POST',
              headers: { 'Authorization': `Bearer ${token}` },
              body: formData
          }).then(r => r.json());
          
          if (res && res.status === 'success') {
              const badge = document.getElementById('verifyBadge');
              badge.className = 'status-badge pending';
              badge.textContent = 'Status: Pending Verification';

              submitBtn.innerHTML = '<i class="ph ph-check-circle"></i> ID Submitted';
              submitBtn.style.color = '#4ade80';
              submitBtn.style.borderColor = '#4ade80';
              
              // Hide the Change button since it's already submitted
              verifyBtn.style.display = 'none'; 
          } else {
              throw new Error(res.message || 'Upload failed');
          }
      } catch (err) {
          alert(err.message || 'Error uploading ID');
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
          verifyBtn.disabled = false;
      }
    }
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

</body>

</html>