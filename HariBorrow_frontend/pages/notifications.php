<?php
// PHP block kept empty for any future auth checks
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HariBorrow — Notifications</title>
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

  .sidebar-footer { padding: 24px; border-top: 1px solid var(--glass-border); }
  .user-profile { display: flex; align-items: center; gap: 12px; }
  .user-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--gold-dark); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; color: var(--bg-deep); }
  .user-info { display: flex; flex-direction: column; }
  .user-name { font-size: 14px; font-weight: 500; color: var(--text-1); }
  .user-dept { font-size: 11px; color: var(--text-3); }

  /* ── MAIN CONTENT ── */
  .main-content {
    flex-grow: 1; height: 100vh; overflow-y: auto; position: relative; z-index: 10;
    padding: 40px 48px; display: flex; flex-direction: column;
  }

  .header-area { margin-bottom: 36px; display: flex; justify-content: space-between; align-items: flex-end; }
  .page-title h1 { font-family: 'Cormorant Garamond', serif; font-size: 42px; font-weight: 400; color: var(--text-1); line-height: 1.1; margin-bottom: 8px; }
  .page-title p { font-size: 14px; color: var(--text-2); font-weight: 300; letter-spacing: 0.03em; }

  .mark-read-btn {
    background: transparent; border: 1px solid var(--glass-border); color: var(--text-2);
    padding: 10px 16px; border-radius: 8px; cursor: pointer;
    font-family: 'Outfit', sans-serif; font-weight: 500; font-size: 13px;
    transition: all 0.25s; display: flex; align-items: center; gap: 8px;
  }
  .mark-read-btn:hover { border-color: var(--gold); color: var(--gold); background: var(--gold-dim); }

  .card {
    background: var(--glass); border: 1px solid var(--glass-border); border-radius: 16px;
    padding: 0; backdrop-filter: blur(16px); flex: 1;
    max-width: 800px; display: flex; flex-direction: column; overflow: hidden;
  }

  .notif-list { flex-grow: 1; overflow-y: auto; }
  
  .notif-item {
    padding: 20px 24px; border-bottom: 1px solid var(--glass-border);
    display: flex; gap: 16px; align-items: flex-start;
    text-decoration: none; transition: background 0.2s;
    cursor: pointer; color: inherit; position: relative;
  }
  .notif-item:last-child { border-bottom: none; }
  .notif-item:hover { background: rgba(255, 255, 255, 0.02); }
  .notif-item.unread { background: rgba(239, 68, 68, 0.04); border-left: 3px solid #ef4444; }
  .notif-item.unread:hover { background: rgba(239, 68, 68, 0.08); }
  .notif-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
  .notif-icon.info { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); }
  .notif-icon.danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
  .notif-icon.warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
  .notif-icon.success { background: rgba(74, 222, 128, 0.1); color: var(--success); border: 1px solid rgba(74, 222, 128, 0.2); }
  
  .notif-content { display: flex; flex-direction: column; gap: 6px; }
  .notif-title { font-size: 15px; font-weight: 500; color: var(--text-1); }
  .notif-desc { font-size: 13px; color: var(--text-2); line-height: 1.5; }
  .notif-time { font-size: 11px; color: var(--text-3); margin-top: 2px; }
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
    <a href="asset_return.php" class="nav-link"><i class="ph ph-arrow-u-up-left"></i> Return an Asset</a>
    <a href="borrowing.php?view=borrows" class="nav-link"><i class="ph ph-clock-counter-clockwise"></i> My Borrowing History</a>
    
    <div style="position: relative;">
      <a href="notifications.php" class="nav-link active" id="notifNavLink">
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
      <h1>Notifications</h1>
      <p>Stay updated with your borrowing requests, approvals, and reminders.</p>
    </div>
    <button class="mark-read-btn" onclick="markAllNotificationsRead(event)">
      <i class="ph ph-check-square-offset"></i> Mark All as Read
    </button>
  </div>

  <div class="card">
    <div class="notif-list" id="notifList">
      <div style="padding: 40px; text-align: center; color: var(--text-2);">Loading notifications...</div>
    </div>
  </div>
</main>

<script src="../js/api.js"></script>
<script src="../js/auth_guard.js"></script>
<script>
  const glow = document.getElementById('glow');
  document.addEventListener('mousemove', e => {
    glow.style.setProperty('--mouse-x', e.clientX + 'px');
    glow.style.setProperty('--mouse-y', e.clientY + 'px');
  });

  async function fetchNotifications() {
      try {
          const response = await window.api.authenticatedFetch('/transactions/notifications.php');
          if (response && response.status === 'success') {
              const notifs = response.notifications;
              const notifList = document.getElementById('notifList');
              const notifBadge = document.getElementById('notifBadge');
              const notifNavLink = document.getElementById('notifNavLink');
              
              notifList.innerHTML = '';
              
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
                  
                  notifs.forEach(notif => {
                      const date = new Date(notif.time_ago.replace(' ', 'T'));
                      const timeAgo = date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                      
                      let iconClass = notif.icon_class || 'ph-bell';
                      if (!notif.icon_class) {
                        if (notif.severity === 'success') iconClass = 'ph-check-circle';
                        if (notif.severity === 'danger') iconClass = 'ph-warning-circle';
                        if (notif.severity === 'warning') iconClass = 'ph-warning';
                      }

                      const item = document.createElement('div');
                      item.className = 'notif-item' + (notif.is_read ? '' : ' unread');
                      item.setAttribute('role', 'button');
                      item.setAttribute('tabindex', '0');
                      item.innerHTML = `
                          <div class="notif-icon ${notif.severity}">
                              <i class="ph ${iconClass}"></i>
                          </div>
                          <div class="notif-content">
                              <span class="notif-title">${notif.title}</span>
                              <span class="notif-desc">${notif.message}</span>
                              <span class="notif-time">${timeAgo}</span>
                          </div>
                      `;
                      
                      item.addEventListener('click', async (e) => {
                          e.preventDefault();
                          e.stopPropagation();
                          if (notif.notification_id && !notif.is_read) {
                              await markNotificationRead(notif.notification_id);
                          }
                          const type = (notif.type || notif.title || '').toLowerCase();
                          if (type.includes('overdue') || type.includes('due soon') || type.includes('return')) {
                              window.location.href = 'asset_return.php';
                          } else if (type.includes('approved') || type.includes('denied') || type.includes('request')) {
                              window.location.href = 'borrowing.php?view=borrows';
                          } else {
                              fetchNotifications();
                          }
                      });
                      notifList.appendChild(item);
                  });
              } else {
                  notifBadge.style.display = 'none';
                  if (notifNavLink) notifNavLink.classList.remove('has-notif');
                  notifList.innerHTML = `
                      <div style="padding: 40px; text-align: center; color: var(--text-3);">
                          <i class="ph ph-bell-slash" style="font-size: 48px; margin-bottom: 16px;"></i>
                          <p>No new notifications.</p>
                      </div>
                  `;
              }
          }
      } catch (error) {
          console.error("Failed to fetch notifications:", error);
          document.getElementById('notifList').innerHTML = `<div style="padding: 40px; text-align: center; color: var(--danger);">Failed to load notifications.</div>`;
      }
  }

  async function markNotificationRead(notificationId) {
      try {
          await window.api.authenticatedFetch('/transactions/notifications_mark_read.php', {
              method: 'PUT',
              body: { notification_id: notificationId }
          });
      } catch (error) {
          console.error("Failed to mark notification read:", error);
      }
  }

  async function markAllNotificationsRead(event) {
      if (event) event.stopPropagation();
      try {
          await window.api.authenticatedFetch('/transactions/notifications_mark_read.php', {
              method: 'PUT',
              body: { mark_all: true }
          });
          await fetchNotifications();
      } catch (error) {
          console.error("Failed to mark all read:", error);
      }
  }

  document.addEventListener('DOMContentLoaded', () => {
      fetchNotifications();
      setInterval(fetchNotifications, 60000);
  });
</script>
</body>
</html>
