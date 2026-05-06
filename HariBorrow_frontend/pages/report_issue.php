<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HariBorrow — Report Issue</title>

  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600&family=Fredoka:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <script src="../js/api.js"></script>
  <script src="../js/auth_guard.js?v=1778041298"></script>
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
      --danger: #ff6b7a;
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
        radial-gradient(circle at 10% 20%, rgba(229, 192, 123, 0.08), transparent 40%),
        radial-gradient(circle at 90% 80%, rgba(255, 107, 122, 0.05), transparent 50%),
        /* red hue for 'Issue' context */
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
      background: radial-gradient(480px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(229, 192, 123, 0.06), transparent 50%);
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

    .profile-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    /* ── MAIN LAYOUT ── */
    .report-container {
      position: relative;
      z-index: 10;
      padding: 140px 5% 60px;
      max-width: 900px;
      margin: 0 auto;
      animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .page-header {
      margin-bottom: 40px;
      text-align: center;
    }

    .page-header h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 48px;
      font-weight: 400;
      color: var(--text-1);
      line-height: 1.1;
    }

    .page-header .aesthetic-script {
      font-family: 'Fredoka', sans-serif;
      font-weight: 700;
      font-size: 1.3em;
      color: var(--gold-light);
      text-shadow: 0 0 20px rgba(229, 192, 123, 0.4);
    }

    .page-header p {
      font-size: 14px;
      color: var(--text-2);
      font-weight: 300;
      margin-top: 12px;
      letter-spacing: 0.03em;
      max-width: 500px;
      margin-inline: auto;
      line-height: 1.6;
    }

    .report-panel {
      background: var(--glass);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border: 1px solid var(--glass-border);
      border-radius: 16px;
      padding: 48px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    }

    .alert {
      display: none;
      align-items: center;
      gap: 12px;
      padding: 16px 20px;
      border-radius: 8px;
      font-size: 14px;
      margin-bottom: 32px;
      letter-spacing: 0.03em;
      font-weight: 500;
    }

    .alert.ok {
      display: flex;
      background: rgba(74, 222, 128, 0.1);
      border: 1px solid rgba(74, 222, 128, 0.3);
      color: #4ade80;
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
      margin-bottom: 24px;
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
    select,
    textarea {
      width: 100%;
      padding: 16px;
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

    textarea {
      resize: vertical;
      min-height: 120px;
      line-height: 1.6;
    }

    input:focus,
    select:focus,
    textarea:focus {
      background: rgba(0, 0, 0, 0.4);
      border-color: var(--gold);
      box-shadow: 0 0 15px rgba(229, 192, 123, 0.15);
    }

    /* Select Dropdown Arrow */
    .select-wrapper {
      position: relative;
    }

    .select-wrapper::after {
      content: '▼';
      font-family: sans-serif;
      font-size: 10px;
      color: var(--text-3);
      position: absolute;
      right: 16px;
      top: 50%;
      transform: translateY(-50%);
      pointer-events: none;
    }

    /* File Upload Area */
    .upload-area {
      border: 1px dashed var(--glass-border);
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.01);
      padding: 32px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 12px;
    }

    .upload-area:hover {
      border-color: var(--gold);
      background: var(--gold-dim);
    }

    .upload-icon {
      font-size: 32px;
      color: var(--gold);
      opacity: 0.8;
    }

    .upload-text {
      font-size: 14px;
      color: var(--text-1);
      font-weight: 400;
    }

    .upload-sub {
      font-size: 11px;
      color: var(--text-3);
      font-weight: 300;
      letter-spacing: 0.05em;
    }

    .file-name-display {
      display: none;
      margin-top: 12px;
      font-size: 13px;
      color: var(--gold-light);
      background: rgba(229, 192, 123, 0.1);
      padding: 8px 16px;
      border-radius: 6px;
      border: 1px solid rgba(229, 192, 123, 0.2);
    }

    .submit-btn {
      width: 100%;
      padding: 18px;
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
      margin-top: 16px;
    }

    .submit-btn::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(90deg, transparent, rgba(229, 192, 123, 0.4), transparent);
      transform: translateX(-100%);
      transition: transform 0.6s ease;
    }

    .submit-btn span {
      position: relative;
      z-index: 1;
      transition: color 0.3s;
    }

    .submit-btn:hover {
      background: var(--gold);
      border-color: var(--gold);
      box-shadow: 0 10px 30px rgba(229, 192, 123, 0.3);
    }

    .submit-btn:hover span {
      color: var(--bg-deep);
      font-weight: 600;
    }

    .submit-btn:hover::before {
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

    @media (max-width: 768px) {
      .form-row {
        grid-template-columns: 1fr;
        gap: 0;
      }

      .report-panel {
        padding: 32px 24px;
      }
    }

    select option {
      background-color: #1a1a22;
      color: #e2ddd6;
      padding: 8px 12px;
      font-family: 'Outfit', sans-serif;
    }

    select option:checked {
      background-color: #2a2a35;
      color: var(--gold-light);
    }
  </style>
  <link rel="stylesheet" href="../css/theme.css?v=1778041298">
</head>

<body>

  <div class="bg-mesh"></div>
  <div class="ambient-glow" id="glow"></div>

  <nav class="top-nav">
    <a href="borrower_lender_dashboard.php" class="nav-brand">
      <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="HariBorrow Logo" class="nav-logo">
      <span class="nav-title">HariBorrow</span>
    </a>
    <div style="display: flex; align-items: center; gap: 16px;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <div class="profile-avatar" id="navAvatar">UN</div>
        <span id="navUserName" style="font-size: 14px; font-weight: 500;">User Name</span>
      </div>
      <a href="borrower_lender_dashboard.php" class="back-btn">
        <i class="ph ph-arrow-left"></i> Dashboard
      </a>
    </div>
  </nav>

  <main class="report-container">
    <div class="page-header">
      <h1>Report an <span class="aesthetic-script">Issue.</span></h1>
      <p>Report damaged equipment, missing accessories, technical gateway errors, or transaction disputes to the
        Pamantasan IT & Facilities team.</p>
    </div>

    <div class="report-panel">

      <div class="alert" id="successAlert">
        <i class="ph ph-check-circle" style="font-size: 24px;"></i>
        <div>
          <strong>Ticket Submitted Successfully.</strong><br>
          <span style="font-weight: 300; font-size: 12px; color: var(--text-2);">Your reference number is #TKT-8902. The
            support team will email you shortly.</span>
        </div>
      </div>

      <form id="reportForm" onsubmit="submitIssue(event)">

        <div class="form-row">
          <div class="field">
            <label>Type of Issue</label>
            <div class="select-wrapper">
              <select required>
                <option value="" disabled selected>Select category...</option>
                <option>Damaged or Defective Equipment</option>
                <option>Missing Accessories (e.g., cables, remotes)</option>
                <option>System/Gateway Technical Bug</option>
                <option>Late Return / Penalty Dispute</option>
                <option>Other / General Inquiry</option>
              </select>
            </div>
          </div>

          <div class="field">
            <label>Related Transaction ID (Optional)</label>
            <input type="text" placeholder="e.g. TXN-8092">
          </div>
        </div>

        <div class="field">
          <label>Subject / Brief Title</label>
          <input type="text" placeholder="e.g. Epson Projector Lens Scratched" required>
        </div>

        <div class="field">
          <label>Detailed Description</label>
          <textarea
            placeholder="Please provide specific details about the issue. If reporting hardware damage, explain how and when it was discovered..."
            required></textarea>
        </div>

        <div class="field">
          <label>Supporting Evidence (Photos / Screenshots)</label>
          <input type="file" id="evidenceUpload" accept="image/*,.pdf" style="display: none;"
            onchange="handleFileSelect(event)">

          <div class="upload-area" onclick="document.getElementById('evidenceUpload').click()">
            <i class="ph ph-image upload-icon"></i>
            <div class="upload-text">Click to browse or drag and drop files</div>
            <div class="upload-sub">Supports JPG, PNG, or PDF (Max 5MB)</div>
          </div>

          <div class="file-name-display" id="fileNameDisplay">
            <i class="ph ph-file"></i> <span id="fileNameText">filename.jpg</span>
            <i class="ph ph-x" style="float: right; cursor: pointer; color: var(--text-2);"
              onclick="clearFile(event)"></i>
          </div>
        </div>

        <button type="submit" class="submit-btn" id="submitBtn">
          <span>Submit Support Ticket</span>
        </button>

      </form>
    </div>
  </main>

  <script>
    // Ambient Mouse Glow
    const glow = document.getElementById('glow');
    document.addEventListener('mousemove', (e) => {
      glow.style.setProperty('--mouse-x', e.clientX + 'px');
      glow.style.setProperty('--mouse-y', e.clientY + 'px');
    });

    // Handle File Upload Display
    function handleFileSelect(event) {
      const fileInput = event.target;
      const fileDisplay = document.getElementById('fileNameDisplay');
      const fileNameText = document.getElementById('fileNameText');
      const uploadArea = document.querySelector('.upload-area');

      if (fileInput.files && fileInput.files.length > 0) {
        fileNameText.textContent = fileInput.files[0].name;
        fileDisplay.style.display = 'block';
        uploadArea.style.borderColor = 'var(--gold)';
      }
    }

    // Clear Selected File
    function clearFile(event) {
      event.stopPropagation(); // Prevent triggering the upload area click
      document.getElementById('evidenceUpload').value = "";
      document.getElementById('fileNameDisplay').style.display = 'none';
      document.querySelector('.upload-area').style.borderColor = 'var(--glass-border)';
    }

    // Submit Simulation
    function submitIssue(event) {
      event.preventDefault();
      const btn = document.getElementById('submitBtn');
      const textSpan = btn.querySelector('span');
      const form = document.getElementById('reportForm');
      const alertBox = document.getElementById('successAlert');

      // Loading State
      textSpan.textContent = 'Encrypting & Submitting...';
      btn.disabled = true;

      // Simulate API Call
      setTimeout(() => {
        form.style.display = 'none';
        alertBox.className = 'alert ok';

        // Scroll to top of panel to see alert
        document.querySelector('.report-panel').scrollIntoView({ behavior: 'smooth', block: 'center' });
      }, 1500);
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

  <script src="../js/theme.js?v=1778041298"></script>
</body>

</html>
