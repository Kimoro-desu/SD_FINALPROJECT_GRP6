<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HariBorrow — User Profile</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700&family=Outfit:wght@300;400;500;600;700&family=Pinyon+Script&display=swap"
    rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <script src="../js/api.js"></script>
  <script src="../js/auth_guard.js"></script>

  <style>
    *, *::before, *::after {
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
      --text-1: #FFFFFF;
      --text-2: #A39E93;
      --text-3: #6B665A;
    }

    html, body {
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

    .bg-mesh {
      position: fixed;
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      z-index: 0;
      background:
        radial-gradient(circle at 20% 20%, rgba(229, 192, 123, 0.08), transparent 40%),
        radial-gradient(circle at 80% 80%, rgba(166, 138, 72, 0.05), transparent 50%),
        var(--bg-deep);
    }

    .top-nav {
      position: fixed;
      top: 0; left: 0; width: 100%; min-height: 80px;
      background: var(--glass-heavy);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border-bottom: 1px solid var(--glass-border);
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
      padding: 12px 5%;
      z-index: 100;
    }

    .nav-brand {
      display: flex; align-items: center; gap: 12px; text-decoration: none;
    }

    .nav-logo {
      height: 52px; width: auto;
      filter: drop-shadow(0 0 10px rgba(229, 192, 123, 0.4));
    }

    .nav-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 22px; font-weight: 600;
      background: linear-gradient(135deg, #FFF 0%, var(--gold-light) 50%, var(--gold-dark) 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }

    .back-btn {
      display: flex; align-items: center; gap: 8px;
      background: transparent; border: 1px solid var(--glass-border);
      color: var(--text-2); padding: 8px 16px; border-radius: 30px;
      font-family: inherit; font-size: 13px; text-decoration: none;
      transition: all 0.3s;
    }
    .back-btn:hover {
      border-color: var(--gold); color: var(--gold); background: rgba(229, 192, 123, 0.1);
    }

    .profile-container {
      position: relative;
      z-index: 10;
      padding: 100px 5% 60px;
      max-width: 1000px;
      margin: 0 auto;
    }

    .profile-card {
      background: var(--glass);
      backdrop-filter: blur(24px);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    }

    .cover-photo-wrapper {
      position: relative;
      width: 100%;
      height: 260px;
      background: linear-gradient(135deg, var(--gold-dark) 0%, rgba(10, 10, 13, 1) 100%);
      border-bottom: 1px solid var(--glass-border);
    }

    .cover-photo {
      width: 100%; height: 100%;
      object-fit: cover;
      display: none;
    }
    .cover-photo.loaded { display: block; }

    .avatar-wrapper.view-only {
      position: absolute;
      top: -56px; left: 40px;
      width: 120px; height: 120px;
      border-radius: 50%;
      border: 4px solid var(--bg-deep);
      background: var(--glass-heavy);
      overflow: hidden;
      display: flex; align-items: center; justify-content: center;
      font-family: 'Cormorant Garamond', serif; font-size: 42px;
      color: var(--gold);
      cursor: default;
    }

    .avatar-wrapper.view-only img {
      width: 100%; height: 100%; object-fit: cover; display: none;
    }
    .avatar-wrapper.view-only img.loaded { display: block; }

    .profile-info-section { padding: 0 40px 40px; position: relative; }

    .profile-details {
      padding-top: 76px;
    }

    .profile-text h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 38px; font-weight: 500;
      margin-bottom: 6px;
    }

    .role-badge {
      display: inline-block;
      font-size: 11px; font-weight: 600; letter-spacing: 0.1em;
      text-transform: uppercase;
      padding: 4px 10px; border-radius: 4px;
      background: rgba(229, 192, 123, 0.1); color: var(--gold);
      border: 1px solid rgba(229, 192, 123, 0.2);
      margin-bottom: 12px;
    }

    .profile-text p {
      font-size: 15px; color: var(--text-2); margin-bottom: 6px;
      display: flex; align-items: center; gap: 8px;
    }

    .rating-row { margin-top: 8px; display: flex; flex-wrap: wrap; gap: 10px; }

    .rating-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      color: var(--gold-light);
      background: rgba(229, 192, 123, 0.1);
      border: 1px solid rgba(229, 192, 123, 0.25);
      border-radius: 16px;
      padding: 5px 10px;
    }

    button.rating-pill.is-clickable {
      cursor: pointer;
      font-family: inherit;
    }
    button.rating-pill.is-clickable:hover {
      background: rgba(229, 192, 123, 0.2);
      border-color: rgba(229, 192, 123, 0.45);
    }

    .err-box {
      padding: 24px;
      color: #ff8a96;
      text-align: center;
    }
  </style>
</head>

<body>
  <div class="bg-mesh"></div>

  <nav class="top-nav">
    <a href="borrower_lender_dashboard.php" class="nav-brand">
      <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Logo" class="nav-logo">
      <span class="nav-title">HariBorrow</span>
    </a>
    <a href="borrower_lender_dashboard.php" class="back-btn"><i class="ph ph-arrow-left"></i> Dashboard</a>
  </nav>

  <main class="profile-container">
    <div class="profile-card" id="profileCard" style="display:none;">
      <div class="cover-photo-wrapper">
        <img src="" alt="" class="cover-photo" id="coverPhotoImg">
      </div>
      <div class="profile-info-section">
        <div class="avatar-wrapper view-only">
          <span id="avatarInitials">UN</span>
          <img src="" alt="" id="profileImg">
        </div>
        <div class="profile-details">
          <div class="profile-text">
            <h1 id="userName">—</h1>
            <div class="role-badge" id="userRole">—</div>
            <p><i class="ph ph-envelope-simple"></i> <span id="userEmail">—</span></p>
            <p><i class="ph ph-identification-badge"></i> <span id="userSchoolId">—</span></p>
            <p><i class="ph ph-buildings"></i> <span id="userDept">—</span></p>
            <div class="rating-row">
              <button type="button" class="rating-pill is-clickable" id="userRatingBtn" title="View all reviews">
                <span id="userRatingLabel"><i class="ph ph-star"></i> —</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="profile-card err-box" id="errState" style="display:none;"></div>
  </main>

  <script>
    (function () {
      const params = new URLSearchParams(window.location.search);
      const targetId = parseInt(params.get('id') || '0', 10);
      const me = window.api && window.api.getUser ? window.api.getUser() : null;
      const myId = me && me.id ? parseInt(String(me.id), 10) : 0;

      document.addEventListener('DOMContentLoaded', async function () {
        if (!targetId) {
          showErr('No user selected.');
          return;
        }
        if (myId && targetId === myId) {
          window.location.replace('my_profile.php');
          return;
        }
        try {
          const data = await window.api.authenticatedFetch('/api/users/public_profile.php?user_id=' + encodeURIComponent(String(targetId)));
          if (!data || data.status !== 'success') throw new Error('Not found');
          const p = data.profile;
          document.getElementById('userName').textContent = p.full_name || '—';
          document.getElementById('userRole').textContent = p.role || '—';
          document.getElementById('userEmail').textContent = p.email || '—';
          document.getElementById('userSchoolId').textContent = p.school_id_number || '—';
          document.getElementById('userDept').textContent = p.department || '—';

          const ratingLabel = document.getElementById('userRatingLabel');
          ratingLabel.innerHTML = '<i class="ph ph-star-fill"></i> ' + Number(p.rating_average || 0).toFixed(2) + ' (' + Number(p.rating_count || 0) + ' ratings)';

          const fi = (p.first_name || '').charAt(0);
          const li = (p.last_name || '').charAt(0);
          document.getElementById('avatarInitials').textContent = (fi + li).toUpperCase() || 'U';

          if (p.profile_picture) {
            const img = document.getElementById('profileImg');
            img.src = p.profile_picture;
            img.classList.add('loaded');
            document.getElementById('avatarInitials').style.display = 'none';
          }
          if (p.background_picture) {
            const bg = document.getElementById('coverPhotoImg');
            bg.src = p.background_picture;
            bg.classList.add('loaded');
          }

          document.getElementById('userRatingBtn').onclick = function () {
            function openRv() {
              if (typeof window.HariBorrowOpenReviewsModal === 'function') {
                window.HariBorrowOpenReviewsModal(p.id, p.full_name || p.email);
              } else {
                setTimeout(openRv, 200);
              }
            }
            openRv();
          };

          document.getElementById('profileCard').style.display = 'block';
        } catch (e) {
          showErr((e && e.message) || 'Could not load this profile.');
        }
      });

      function showErr(msg) {
        const el = document.getElementById('errState');
        el.textContent = msg;
        el.style.display = 'block';
      }
    })();
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
