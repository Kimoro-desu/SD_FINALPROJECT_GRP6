<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HariBorrow — Registration Approvals</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600&family=Fredoka:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="../js/api.js"></script>
<script src="../js/auth_guard.js?v=1778041298"></script>
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg-deep:    #030304;
    --glass:      rgba(15, 15, 20, 0.45);
    --glass-heavy: rgba(10, 10, 13, 0.85);
    --glass-border: rgba(255, 255, 255, 0.08);
    --gold:       #E5C07B;
    --gold-light: #FCEBAF;
    --gold-dark:  #A68A48;
    --gold-dim:   rgba(229, 192, 123, 0.1);
    --text-1:     #FFFFFF;
    --text-2:     #A39E93;
    --text-3:     #6B665A;
    --sidebar-w:  260px;
    --danger:     #ff6b7a;
    --success:    #4ade80;
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

  /* Background */
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
    background: radial-gradient(480px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(229, 192, 123, 0.06), transparent 50%);
    z-index: 9999; mix-blend-mode: screen; transition: background 0.08s ease-out;
  }

  /* Sidebar */
  .sidebar {
    width: var(--sidebar-w); height: 100vh;
    background: var(--glass-heavy); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
    border-right: 1px solid var(--glass-border);
    display: flex; flex-direction: column; z-index: 100;
    position: relative;
  }

  .sidebar-header {
    padding: 32px 24px; border-bottom: 1px solid var(--glass-border);
    display: flex; align-items: center; gap: 12px;
  }
  
  .nav-logo { 
    height: 48px; width: auto; object-fit: contain;
    filter: drop-shadow(0 0 10px rgba(229, 192, 123, 0.4)) brightness(1.2) contrast(1.3) saturate(1.4);
  }

  .nav-title {
    font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 600;
    background: linear-gradient(135deg, #FFF 0%, var(--gold-light) 50%, var(--gold-dark) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 0.02em;
    line-height: 1;
  }
  
  .admin-badge {
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
  .nav-link.active { background: var(--gold-dim); border-color: rgba(229, 192, 123, 0.2); color: var(--gold-light); }
  .nav-link.active i { color: var(--gold); }

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
    margin-left: auto; background: var(--danger); color: #fff;
    font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px;
    animation: badgePulse 2s ease-in-out infinite;
    box-shadow: 0 0 8px rgba(239, 68, 68, 0.4);
  }

  @keyframes badgePulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 8px rgba(239, 68, 68, 0.4); }
    50% { transform: scale(1.1); box-shadow: 0 0 14px rgba(239, 68, 68, 0.6); }
  }

  .sidebar-footer { padding: 24px; border-top: 1px solid var(--glass-border); }
  .admin-profile { display: flex; align-items: center; gap: 12px; }
  .admin-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--gold-dark); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; color: var(--bg-deep); }
  .admin-info { display: flex; flex-direction: column; }
  .admin-name { font-size: 14px; font-weight: 500; color: var(--text-1); }
  .admin-role { font-size: 11px; color: var(--text-3); }

  /* Main Layout */
  .main-content {
    flex-grow: 1; height: 100vh; overflow-y: auto; position: relative; z-index: 10;
    padding: 40px 48px;
  }

  .header-area { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px; }
  .page-title h1 { font-family: 'Cormorant Garamond', serif; font-size: 42px; font-weight: 400; color: var(--text-1); line-height: 1.1; margin-bottom: 8px; }
  .page-title p { font-size: 14px; color: var(--text-2); font-weight: 300; letter-spacing: 0.03em; }

  /* System Alerts */
  .sys-alert {
    padding: 16px 20px; border-radius: 8px; margin-bottom: 30px; font-size: 13px; font-weight: 400;
    display: flex; align-items: center; gap: 12px; animation: slideDown 0.4s ease;
  }
  .sys-alert.success { background: rgba(74, 222, 128, 0.1); border: 1px solid rgba(74, 222, 128, 0.3); color: var(--success); }
  .sys-alert.error { background: rgba(255, 107, 122, 0.1); border: 1px solid rgba(255, 107, 122, 0.3); color: var(--danger); }
  @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

  /* Data Table Extensions */
  .data-panel {
    background: var(--glass); border: 1px solid var(--glass-border); border-radius: 12px;
    overflow: hidden; backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
  }
  
  .panel-header {
    padding: 24px; border-bottom: 1px solid var(--glass-border);
    display: flex; justify-content: space-between; align-items: center;
  }
  .panel-title { font-size: 16px; font-weight: 500; color: var(--text-1); letter-spacing: 0.02em; }
  
  table { width: 100%; border-collapse: collapse; }
  th {
    text-align: left; padding: 16px 24px; font-size: 10px; font-weight: 600;
    letter-spacing: 0.15em; text-transform: uppercase; color: var(--text-3);
    border-bottom: 1px solid var(--glass-border); background: rgba(0,0,0,0.2);
  }
  td {
    padding: 16px 24px; font-size: 14px; color: var(--text-1); font-weight: 400;
    border-bottom: 1px solid rgba(255,255,255,0.03); vertical-align: middle;
  }
  tr:hover td { background: rgba(255,255,255,0.02); }

  .sub-text { font-size: 12px; color: var(--text-3); display: block; margin-top: 4px; font-weight: 300; }
  
  /* Role Tags */
  .role-tag {
    display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px;
    border-radius: 4px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em;
    text-transform: uppercase; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border);
  }
  .role-tag.student { color: #8ab4f8; border-color: rgba(138, 180, 248, 0.2); background: rgba(138, 180, 248, 0.05); }
  .role-tag.faculty { color: var(--gold); border-color: rgba(229, 192, 123, 0.2); background: rgba(229, 192, 123, 0.05); }
  .role-tag.staff { color: #c084fc; border-color: rgba(192, 132, 252, 0.2); background: rgba(192, 132, 252, 0.05); }
  .role-tag.researcher { color: #4ade80; border-color: rgba(74, 222, 128, 0.2); background: rgba(74, 222, 128, 0.05); }

  /* Action Buttons */
  .action-form { display: flex; gap: 8px; }
  .btn-action {
    background: transparent; border: 1px solid var(--glass-border); padding: 8px 12px;
    border-radius: 6px; font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 600;
    letter-spacing: 0.1em; text-transform: uppercase; cursor: pointer; transition: all 0.2s;
    display: flex; align-items: center; gap: 6px;
  }
  .btn-approve { color: var(--success); border-color: rgba(74, 222, 128, 0.3); }
  .btn-approve:hover { background: rgba(74, 222, 128, 0.1); border-color: var(--success); }
  
  .btn-reject { color: var(--danger); border-color: rgba(255, 107, 122, 0.3); }
  .btn-reject:hover { background: rgba(255, 107, 122, 0.1); border-color: var(--danger); }

  /* Empty State */
  .empty-state { text-align: center; padding: 60px 20px; color: var(--text-2); }
  .empty-state i { font-size: 48px; color: var(--glass-border); margin-bottom: 16px; display: block; }
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
      <a href="active_transactions.php" class="nav-link"><i class="ph ph-clock-counter-clockwise"></i> All Transactions</a>
    <div class="nav-section-title">Database</div>
    <a href="asset_inventory.php" class="nav-link"><i class="ph ph-stack"></i> Asset Inventory</a>
    <a href="registered_users.php" class="nav-link"><i class="ph ph-users"></i> Registered Users</a>
    <div class="nav-section-title">System</div>
    <a href="registration_approval.php" class="nav-link active">
      <i class="ph ph-shield-check"></i> Registration Approvals
    </a>
    <a href="system_logs.php" class="nav-link"><i class="ph ph-file-text"></i> System Logs</a>
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
      <h1>Registration Approvals</h1>
      <p>Review and verify identity credentials for new system applicants.</p>
    </div>
  </div>

  <div class="sys-alert success" style="display: none;" id="sysAlert">
    <i class="ph ph-check-circle"></i> <span id="sysAlertMsg">Update successful.</span>
  </div>

  <div class="data-panel">
    <div class="panel-header">
      <div class="panel-title">New Account Applications</div>
    </div>
    <table>
      <thead>
        <tr>
          <th>Applicant</th>
          <th>Academic Profile</th>
          <th>Role</th>
          <th>Contact / Email</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="newAccountsTbody"></tbody>
    </table>
  </div>

  <div class="data-panel" style="margin-top: 32px;">
    <div class="panel-header">
      <div class="panel-title">ID Verification Queue</div>
    </div>
    <table>
      <thead>
        <tr>
          <th>Applicant</th>
          <th>Academic Profile</th>
          <th>Role</th>
          <th>ID Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="idVerificationTbody"></tbody>
    </table>
  </div>

</main>

<script>
  /* ── Ambient Mouse Glow (Inherited from Admin Console) ── */
  const glow = document.getElementById('glow');
  document.addEventListener('mousemove', (e) => {
    glow.style.setProperty('--mouse-x', e.clientX + 'px');
    glow.style.setProperty('--mouse-y', e.clientY + 'px');
  });

  function showAlert(msg, type) {
    const alertEl = document.getElementById('sysAlert');
    const msgEl = document.getElementById('sysAlertMsg');
    if (!alertEl || !msgEl) return;
    msgEl.textContent = msg;
    alertEl.className = 'sys-alert ' + type;
    alertEl.style.opacity = '1';
    alertEl.style.display = 'flex';
    setTimeout(() => {
      alertEl.style.opacity = '0';
      setTimeout(() => alertEl.style.display = 'none', 300);
    }, 3500);
  }

  function titleCase(val) {
    const s = String(val || '').trim().toLowerCase();
    return s ? (s.charAt(0).toUpperCase() + s.slice(1)) : '';
  }

  function roleTag(role) {
    const r = String(role || '').trim().toLowerCase();
    const cls = r === 'student' ? 'student' : r === 'faculty' ? 'faculty' : r === 'staff' ? 'staff' : 'researcher';
    const icon = r === 'student' ? 'ph-student' : r === 'faculty' ? 'ph-chalkboard-teacher' : r === 'staff' ? 'ph-identification-card' : 'ph-microscope';
    return `<span class="role-tag ${cls}"><i class="ph ${icon}"></i> ${titleCase(r) || 'User'}</span>`;
  }

  function resolveFileUrl(rawUrl) {
    const val = String(rawUrl || '').trim();
    if (!val) return '';
    if (/^https?:\/\//i.test(val)) return val;
    if (val.includes('SD_FINALPROJECT_GRP6/HariBorrow_backend')) return val.startsWith('/') ? val : '/' + val;
    return `/SD_FINALPROJECT_GRP6/HariBorrow_backend/${val.replace(/^\/+/, '')}`;
  }

async function processRequest(userId, action, requestId = null) {
    let bodyData = {};
    let confirmMsg = '';

    if (action === 'approve_account') {
        confirmMsg = 'Approve this application and fully activate the user account?';
        bodyData = { user_id: userId, account_status: 'active' };
    } else if (action === 'reject_account') {
        confirmMsg = 'Are you sure you want to completely reject and suspend this user account?';
        bodyData = { user_id: userId, account_status: 'suspended', admin_notes: 'Registration rejected by admin.' };
    } else if (action === 'verify_id') {
        confirmMsg = 'Approve this ID photo?';
        bodyData = { user_id: userId, id_verification_status: 'verified' };
    } else if (action === 'reject_id') {
        confirmMsg = 'Reject this ID photo? The user will need to upload a new one.';
        bodyData = { user_id: userId, id_verification_status: 'rejected' };
    }

    if (!confirm(confirmMsg)) return;

    try {
      // Keep registration request state in sync with account actions.
      if ((action === 'approve_account' || action === 'reject_account') && requestId) {
        const registrationAction = action === 'approve_account' ? 'approve' : 'reject';
        const regRes = await window.api.authenticatedFetch('/api/admin/registration_process.php', {
          method: 'POST',
          body: { request_id: Number(requestId), action: registrationAction }
        });
        if (regRes?.status !== 'success') {
          showAlert(regRes?.message || 'Failed to process registration request.', 'error');
          return;
        }
      }

      const res = await window.api.authenticatedFetch('/api/users/update_account_status.php', {
        method: 'POST',
        body: bodyData
      });
      if (res?.status === 'success') {
        showAlert(`Action completed successfully.`, 'success');
        await loadPending();
      } else {
        showAlert(res?.message || 'Failed to update user.', 'error');
      }
    } catch (e) {
      showAlert(e?.message || 'Failed to update user.', 'error');
    }
  }

  async function loadPending() {
    const newAccBody = document.getElementById('newAccountsTbody');
    const idVerBody = document.getElementById('idVerificationTbody');
    if (!newAccBody || !idVerBody) return;

    try {
      const [usersRes, regPendingRes] = await Promise.all([
        window.api.authenticatedFetch('/api/users/list.php'),
        window.api.authenticatedFetch('/api/admin/registration_pending.php')
      ]);
      const allUsers = Array.isArray(usersRes?.users) ? usersRes.users : [];
      const pendingRegistrations = Array.isArray(regPendingRes?.pending) ? regPendingRes.pending : [];
      
      // Keep statuses normalized so queue separation is consistent.
      const normalizedUsers = allUsers.map(u => ({
        ...u,
        account_status: String(u.account_status || '').trim().toLowerCase(),
        id_verification_status: String(u.id_verification_status || '').trim().toLowerCase(),
        role_normalized: String(u.role || '').trim().toLowerCase()
      }));

      // 1. New Accounts: source from registration_requests (real pending approvals queue).
      const newAccounts = pendingRegistrations.map(item => {
        const u = item?.user || {};
        return {
          request_id: item?.request_id,
          id: u.id,
          school_id: u.school_id,
          name: u.name,
          department: u.department,
          role: u.role,
          email: u.email
        };
      });
      const pendingRegistrationUserIds = new Set(
        newAccounts.map(u => Number(u.id)).filter(id => Number.isFinite(id) && id > 0)
      );

      // 2. ID Verification Queue:
      //    - include users awaiting ID review (unverified or pending)
      //    - keep users with pending account approval out of this queue
      const idVerifications = normalizedUsers.filter(u =>
        (u.id_verification_status === 'unverified' || u.id_verification_status === 'pending') &&
        u.account_status !== 'suspended' &&
        u.role_normalized !== 'admin' &&
        !pendingRegistrationUserIds.has(Number(u.id))
      );

      // Render New Accounts Table
      newAccBody.innerHTML = newAccounts.map(u => {
        const name = u.name || '—';
        const schoolId = u.school_id || '—';
        const dept = u.department || '—';
        const email = u.email || '—';
        const role = u.role || '';

        return `
          <tr>
            <td>${name}<br><span class="sub-text" style="font-family: monospace; color: var(--gold);">ID: ${schoolId}</span></td>
            <td>${dept}</td>
            <td>${roleTag(role)}</td>
            <td>${email}</td>
            <td>
              <div class="action-form" style="display: flex; gap: 8px;">
                <button type="button" class="btn-action btn-approve" onclick="processRequest(${u.id}, 'approve_account', ${u.request_id})">
                  <i class="ph ph-check-circle"></i> Approve Account
                </button>
                <button type="button" class="btn-action btn-reject" onclick="processRequest(${u.id}, 'reject_account', ${u.request_id})">
                  <i class="ph ph-user-minus"></i> Reject
                </button>
              </div>
            </td>
          </tr>
        `;
      }).join('') || `<tr><td colspan="5" class="empty-state"><i class="ph ph-check-circle"></i> No new account applications!</td></tr>`;

      // Render ID Verifications Table
      idVerBody.innerHTML = idVerifications.map(u => {
        const name = u.name || '—';
        const schoolId = u.school_id || '—';
        const dept = u.department || '—';
        const role = u.role || '';
        
        const idPhotoUrl = resolveFileUrl(u.id_photo_url);
        const isPendingReview = String(u.id_verification_status).toLowerCase() === 'pending';
        const idStatusLabel = isPendingReview ? 'Pending Review' : 'Unverified';
        let idLink = idPhotoUrl
            ? `<br><a href="${idPhotoUrl}" target="_blank" rel="noopener noreferrer" style="color: var(--success); font-size: 11px; text-decoration: underline; margin-top: 4px; display: inline-block;"><i class="ph ph-image"></i> View Uploaded ID Photo</a>`
            : `<br><span style="color: var(--text-3); font-size: 11px; margin-top: 4px; display: inline-block;"><i class="ph ph-image-broken"></i> No uploaded ID photo</span>`;

        return `
          <tr>
            <td>${name}<br><span class="sub-text" style="font-family: monospace; color: var(--gold);">ID: ${schoolId}</span></td>
            <td>${dept}</td>
            <td>${roleTag(role)}</td>
            <td>
                <span style="color: var(--gold); font-size: 12px;"><i class="ph ph-hourglass"></i> ${idStatusLabel}</span>
                ${idLink}
            </td>
            <td>
              <div class="action-form" style="display: flex; gap: 8px;">
                <button type="button" class="btn-action btn-approve" onclick="processRequest(${u.id}, 'verify_id')">
                  <i class="ph ph-identification-card"></i> Verify ID
                </button>
                <button type="button" class="btn-action btn-reject" onclick="processRequest(${u.id}, 'reject_id')">
                  <i class="ph ph-x"></i> Reject ID
                </button>
              </div>
            </td>
          </tr>
        `;
      }).join('') || `<tr><td colspan="5" class="empty-state"><i class="ph ph-check-circle"></i> No pending ID verifications!</td></tr>`;

    } catch (e) {
      console.error('Pending approvals load failed:', e);
      newAccBody.innerHTML = `<tr><td colspan="5" class="empty-state"><i class="ph ph-warning"></i> Failed to load applications.</td></tr>`;
      idVerBody.innerHTML = `<tr><td colspan="5" class="empty-state"><i class="ph ph-warning"></i> Failed to load verifications.</td></tr>`;
    }
  }

  document.addEventListener('DOMContentLoaded', loadPending);
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