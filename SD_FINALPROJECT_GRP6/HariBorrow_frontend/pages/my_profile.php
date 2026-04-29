<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HariBorrow — My Profile</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&family=Pinyon+Script&display=swap"
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
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      z-index: 0;
      background:
        radial-gradient(circle at 20% 20%, rgba(229, 192, 123, 0.08), transparent 40%),
        radial-gradient(circle at 80% 80%, rgba(166, 138, 72, 0.05), transparent 50%),
        var(--bg-deep);
    }

    /* ── TOP NAVIGATION ── */
    .top-nav {
      position: fixed;
      top: 0; left: 0; width: 100%; height: 80px;
      background: var(--glass-heavy);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border-bottom: 1px solid var(--glass-border);
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 5%;
      z-index: 100;
    }

    .nav-brand {
      display: flex; align-items: center; gap: 12px; text-decoration: none;
    }

    .nav-logo {
      height: 56px; width: auto; object-fit: contain;
      filter: drop-shadow(0 0 10px rgba(229, 192, 123, 0.4)) brightness(1.2);
    }

    .nav-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 24px; font-weight: 600;
      background: linear-gradient(135deg, #FFF 0%, var(--gold-light) 50%, var(--gold-dark) 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }

    .back-btn {
      display: flex; align-items: center; gap: 8px;
      background: transparent; border: 1px solid var(--glass-border);
      color: var(--text-2); padding: 8px 16px; border-radius: 30px;
      font-family: 'Outfit', sans-serif; font-size: 13px; text-decoration: none;
      transition: all 0.3s;
    }

    .back-btn:hover {
      border-color: var(--gold); color: var(--gold); background: rgba(229, 192, 123, 0.1);
    }

    /* ── PROFILE CONTAINER ── */
    .profile-container {
      position: relative;
      z-index: 10;
      padding: 100px 5% 60px;
      max-width: 1000px;
      margin: 0 auto;
      animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .profile-card {
      background: var(--glass);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    }

    /* Cover Photo */
    .cover-photo-wrapper {
      position: relative;
      width: 100%;
      height: 280px;
      background: linear-gradient(135deg, var(--gold-dark) 0%, rgba(10, 10, 13, 1) 100%);
      border-bottom: 1px solid var(--glass-border);
    }

    .cover-photo {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: none;
    }

    .cover-photo.loaded {
      display: block;
    }

    .edit-cover-btn {
      position: absolute;
      bottom: 16px; right: 16px;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(10px);
      border: 1px solid var(--glass-border);
      color: var(--text-1);
      padding: 8px 16px;
      border-radius: 8px;
      font-size: 13px; font-weight: 500;
      cursor: pointer; transition: all 0.2s;
      display: flex; align-items: center; gap: 8px;
    }

    .edit-cover-btn:hover {
      background: var(--gold);
      color: var(--bg-deep);
      border-color: var(--gold);
    }

    /* Profile Info Section */
    .profile-info-section {
      padding: 0 40px 40px;
      position: relative;
    }

    /* Avatar */
    .avatar-wrapper {
      position: absolute;
      top: -60px; left: 40px;
      width: 140px; height: 140px;
      border-radius: 50%;
      border: 4px solid var(--bg-deep);
      background: var(--glass-heavy);
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
      cursor: pointer;
      overflow: hidden;
      display: flex; align-items: center; justify-content: center;
      font-family: 'Cormorant Garamond', serif; font-size: 48px; color: var(--gold);
    }

    .avatar-wrapper img {
      width: 100%; height: 100%;
      object-fit: cover;
      display: none;
    }

    .avatar-wrapper img.loaded {
      display: block;
    }

    .avatar-overlay {
      position: absolute;
      inset: 0;
      background: rgba(0,0,0,0.6);
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      opacity: 0; transition: opacity 0.3s;
      color: var(--text-1); font-size: 12px; font-weight: 500; font-family: 'Outfit', sans-serif;
    }

    .avatar-wrapper:hover .avatar-overlay {
      opacity: 1;
    }

    /* Details */
    .profile-details {
      padding-top: 90px;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
    }

    .profile-text h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 42px; font-weight: 500; color: var(--text-1);
      margin-bottom: 4px; line-height: 1;
    }

    .profile-text .role-badge {
      display: inline-block;
      font-size: 11px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase;
      padding: 4px 10px; border-radius: 4px;
      background: rgba(229, 192, 123, 0.1); color: var(--gold);
      border: 1px solid rgba(229, 192, 123, 0.2);
      margin-bottom: 12px;
    }

    .profile-text p {
      font-size: 15px; color: var(--text-2); margin-bottom: 6px;
      display: flex; align-items: center; gap: 8px;
    }

    .rating-row {
      margin-top: 6px;
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }

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

    .action-buttons {
      display: flex; gap: 12px;
    }

    .btn-primary {
      padding: 10px 24px;
      background: var(--gold-dim); border: 1px solid var(--gold);
      color: var(--gold); border-radius: 8px;
      font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.3s;
      text-decoration: none;
    }

    .btn-primary:hover {
      background: var(--gold); color: var(--bg-deep); font-weight: 600;
    }

    /* Loading overlay for uploads */
    .upload-overlay {
      position: fixed; inset: 0;
      background: rgba(0,0,0,0.8); z-index: 99999;
      display: flex; align-items: center; justify-content: center; flex-direction: column;
      opacity: 0; pointer-events: none; transition: opacity 0.3s;
    }
    .upload-overlay.active {
      opacity: 1; pointer-events: auto;
    }
    .spinner {
      width: 40px; height: 40px; border: 3px solid rgba(229, 192, 123, 0.3);
      border-top-color: var(--gold); border-radius: 50%;
      animation: spin 1s linear infinite; margin-bottom: 16px;
    }
    @keyframes spin { 100% { transform: rotate(360deg); } }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>

<body>
  <div class="bg-mesh"></div>

  <nav class="top-nav">
    <a href="borrower_lender_dashboard.php" class="nav-brand">
      <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="HariBorrow Logo" class="nav-logo">
      <span class="nav-title">HariBorrow</span>
    </a>
    <div style="display: flex; align-items: center; gap: 16px;">
      <a href="borrower_lender_dashboard.php" class="back-btn">
        <i class="ph ph-arrow-left"></i> Dashboard
      </a>
    </div>
  </nav>

  <main class="profile-container">
    <div class="profile-card">
      <div class="cover-photo-wrapper">
        <img src="" alt="Cover Photo" class="cover-photo" id="coverPhotoImg">
        <button class="edit-cover-btn" onclick="document.getElementById('bgInput').click()">
          <i class="ph ph-camera"></i> Edit Cover Photo
        </button>
      </div>

      <div class="profile-info-section">
        <div class="avatar-wrapper" onclick="document.getElementById('avatarInput').click()">
          <span id="avatarInitials">UN</span>
          <img src="" alt="Profile Picture" id="profileImg">
          <div class="avatar-overlay">
            <i class="ph ph-camera" style="font-size: 24px; margin-bottom: 4px;"></i>
            Upload
          </div>
        </div>

        <div class="profile-details">
          <div class="profile-text">
            <h1 id="userName">Loading...</h1>
            <div class="role-badge" id="userRole">ROLE</div>
            <p><i class="ph ph-envelope-simple"></i> <span id="userEmail">loading@plm.edu.ph</span></p>
            <p><i class="ph ph-identification-card"></i> Student / Employee</p>
            <div class="rating-row">
              <span class="rating-pill" id="userRating"><i class="ph ph-star"></i> 0.00 (0 ratings)</span>
              <span class="rating-pill" id="userPoints"><i class="ph ph-coins"></i> 0 points</span>
            </div>
          </div>
          
        </div>
      </div>
    </div>
  </main>

  <input type="file" id="bgInput" accept="image/*" style="display: none;" onchange="handleFileUpload(event, 'background_picture')">
  <input type="file" id="avatarInput" accept="image/*" style="display: none;" onchange="handleFileUpload(event, 'profile_picture')">

  <div class="upload-overlay" id="uploadOverlay">
    <div class="spinner"></div>
    <div style="color: var(--gold); font-size: 14px; letter-spacing: 0.1em; text-transform: uppercase;">Uploading...</div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', async () => {
      await loadProfile();
    });

    async function loadProfile() {
      try {
        const data = await window.api.authenticatedFetch('/api/users/profile.php');
        if (data && data.status === 'success') {
          const p = data.profile;
          
          document.getElementById('userName').textContent = p.full_name;
          document.getElementById('userRole').textContent = p.role;
          document.getElementById('userEmail').textContent = p.email;
          document.getElementById('userRating').innerHTML = `<i class="ph ph-star-fill"></i> ${Number(p.rating_average || 0).toFixed(2)} (${Number(p.rating_count || 0)} ratings)`;
          document.getElementById('userPoints').innerHTML = `<i class="ph ph-coins"></i> ${Number(p.reward_points || 0)} points`;
          
          // Initials
          const initials = p.first_name.charAt(0) + (p.last_name ? p.last_name.charAt(0) : '');
          document.getElementById('avatarInitials').textContent = initials;

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
        }
      } catch (err) {
        console.error("Failed to load profile:", err);
      }
    }

    async function handleFileUpload(event, type) {
      const file = event.target.files[0];
      if (!file) return;

      const overlay = document.getElementById('uploadOverlay');
      overlay.classList.add('active');

      const formData = new FormData();
      formData.append(type, file);

      try {
        const token = window.api.getToken();
        const response = await fetch('/SD_FINALPROJECT_GRP6/HariBorrow_backend/api/users/upload_pictures.php', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`
          },
          body: formData
        });

        const data = await response.json();
        
        if (data.status === 'success') {
          if (type === 'profile_picture' && data.profile_picture) {
            const img = document.getElementById('profileImg');
            img.src = data.profile_picture;
            img.classList.add('loaded');
            document.getElementById('avatarInitials').style.display = 'none';
            // Update global user info in local storage so other pages see it
            let user = window.api.getUser();
            if(user) {
              user.profile_picture = data.profile_picture;
              window.api.setUser(user);
            }
          }
          if (type === 'background_picture' && data.background_picture) {
            const bg = document.getElementById('coverPhotoImg');
            bg.src = data.background_picture;
            bg.classList.add('loaded');
          }
        } else {
          alert('Upload failed: ' + (data.message || 'Unknown error'));
        }
      } catch (err) {
        console.error("Upload error:", err);
        alert('An error occurred during upload.');
      } finally {
        overlay.classList.remove('active');
        event.target.value = ''; // Reset input
      }
    }
  </script>
</body>
</html>
