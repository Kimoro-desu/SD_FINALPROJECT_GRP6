<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HariBorrow — Registration Approvals</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600&family=Fredoka:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="../js/api.js"></script>
<script src="../js/auth_guard.js"></script>
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
    background: radial-gradient(600px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(229, 192, 123, 0.04), transparent 50%);
    z-index: 9999; mix-blend-mode: screen; transition: background 0.1s;
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
      <span class="nav-badge" id="regBadge" style="display:none;">0</span> 
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
      <div class="panel-title">Pending Applications</div>
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
      <tbody id="pendingTbody"></tbody>
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

  async function processRequest(requestId, action) {
    const ok = action === 'reject'
      ? confirm('Are you sure you want to reject this application? This action cannot be undone.')
      : true;
    if (!ok) return;

    try {
      const res = await window.api.authenticatedFetch('/api/admin/registration_process.php', {
        method: 'POST',
        body: { request_id: requestId, action }
      });
      if (res?.status === 'success') {
        showAlert(`Request ${action}d successfully.`, 'success');
        await loadPending();
      } else {
        showAlert(res?.message || 'Failed to update request.', 'error');
      }
    } catch (e) {
      showAlert(e?.message || 'Failed to update request.', 'error');
    }
  }

  async function loadPending() {
    const tbody = document.getElementById('pendingTbody');
    const badge = document.getElementById('regBadge');
    if (!tbody) return;

    try {
      const res = await window.api.authenticatedFetch('/api/admin/registration_pending.php');
      const pending = Array.isArray(res?.pending) ? res.pending : [];

      if (badge) {
        badge.textContent = String(pending.length);
        badge.style.display = pending.length ? 'inline-flex' : 'none';
      }

      tbody.innerHTML = pending.map(item => {
        const u = item.user || {};
        const name = u.name || '—';
        const schoolId = u.school_id || '—';
        const dept = u.department || '—';
        const email = u.email || '—';
        const contact = u.contact || 'Not provided';
        const role = u.role || '';
        return `
          <tr>
            <td>
              ${name}
              <span class="sub-text" style="font-family: monospace; color: var(--gold);">ID: ${schoolId}</span>
            </td>
            <td>${dept}</td>
            <td>${roleTag(role)}</td>
            <td>
              ${email}
              <span class="sub-text">${contact}</span>
            </td>
            <td>
              <div class="action-form">
                <button type="button" class="btn-action btn-approve" onclick="processRequest(${item.request_id}, 'approve')">
                  <i class="ph ph-check"></i> Approve
                </button>
                <button type="button" class="btn-action btn-reject" onclick="processRequest(${item.request_id}, 'reject')">
                  <i class="ph ph-x"></i> Reject
                </button>
              </div>
            </td>
          </tr>
        `;
      }).join('') || `
        <tr>
          <td colspan="5" class="empty-state">
            <i class="ph ph-inbox"></i>
            No pending applications right now.
          </td>
        </tr>
      `;
    } catch (e) {
      console.error('Pending approvals load failed:', e);
      tbody.innerHTML = `
        <tr>
          <td colspan="5" class="empty-state">
            <i class="ph ph-warning"></i>
            Failed to load pending applications.
          </td>
        </tr>
      `;
    }
  }

  // Dynamic Admin Profile Loading (same logic as other admin pages)
  document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
      let user = window.api ? window.api.getUser() : null;
      if (user && user.name) {
        const firstName = user.name.split(' ')[0] || 'Admin';
        const lastName = user.name.split(' ').slice(1).join(' ') || '';
        const initial = firstName.charAt(0).toUpperCase();

        const avatar = document.getElementById('adminAvatar');
        if (avatar) avatar.textContent = initial;

        const nameSpan = document.getElementById('adminName');
        if (nameSpan) nameSpan.textContent = `${firstName} ${lastName}`.trim();

        const roleSpan = document.getElementById('adminRole');
        if (roleSpan) {
          const rawRole = (user.role || '').trim().toLowerCase();
          roleSpan.textContent = rawRole ? (rawRole.charAt(0).toUpperCase() + rawRole.slice(1)) : 'Admin';
        }
      }
    }, 300);
  });

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
  <script src="../js/theme.js"></script>

</body>
</html>
