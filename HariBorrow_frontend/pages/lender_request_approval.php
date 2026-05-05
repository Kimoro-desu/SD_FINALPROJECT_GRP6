<?php require_once '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HariBorrow — Lender Confirmations</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600&family=Fredoka:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<script src="../js/api.js"></script>
<script src="../js/auth_guard.js"></script>
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
    --gold-glow:  rgba(229, 192, 123, 0.25);
    --text-1:     #FFFFFF;
    --text-2:     #A39E93;
    --text-3:     #6B665A;
    --sidebar-w:  260px;
    --danger:     #ff6b7a;
    --danger-dim: rgba(255, 107, 122, 0.1);
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
    transition: transform 0.3s ease, filter 0.3s ease;
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
    font-size: 14px; font-weight: 400; transition: all 0.2s; border: 1px solid transparent;
  }
  
  .nav-link i { font-size: 20px; color: var(--text-3); transition: color 0.2s; }
  .nav-link:hover { background: rgba(255,255,255,0.03); color: var(--text-1); }
  .nav-link.active { background: var(--gold-dim); border-color: rgba(229, 192, 123, 0.2); color: var(--gold-light); }
  .nav-link.active i { color: var(--gold); }

  .sidebar-footer { padding: 24px; border-top: 1px solid var(--glass-border); }
  .admin-profile { display: flex; align-items: center; gap: 12px; }
  .admin-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--gold-dark); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; color: var(--bg-deep); }
  .admin-info { display: flex; flex-direction: column; }
  .admin-name { font-size: 14px; font-weight: 500; color: var(--text-1); }
  .admin-role { font-size: 11px; color: var(--text-3); text-transform: capitalize; }

  .main-content {
    flex-grow: 1; height: 100vh; overflow-y: auto; position: relative; z-index: 10;
    padding: 40px 48px;
  }

  .header-area { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px; }
  .page-title h1 { font-family: 'Cormorant Garamond', serif; font-size: 42px; font-weight: 400; color: var(--text-1); line-height: 1.1; margin-bottom: 8px; }
  .page-title p { font-size: 14px; color: var(--text-2); font-weight: 300; letter-spacing: 0.03em; }

  .toolbar {
    display: flex; align-items: center; gap: 12px; margin-bottom: 24px; flex-wrap: wrap;
  }
  .search-wrap { position: relative; flex: 1; min-width: 220px; max-width: 400px; }
  .search-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-3); font-size: 18px; pointer-events: none; }
  .search-input {
    width: 100%; background: var(--glass); border: 1px solid var(--glass-border);
    border-radius: 10px; padding: 11px 16px 11px 42px;
    color: var(--text-1); font-family: 'Outfit', sans-serif; font-size: 13px; outline: none;
    transition: border-color 0.2s;
  }
  .search-input::placeholder { color: var(--text-3); }
  .search-input:focus { border-color: rgba(229,192,123,0.4); }

  .filter-select {
    background: var(--glass); border: 1px solid var(--glass-border); border-radius: 10px;
    padding: 11px 36px 11px 14px; color: var(--text-2); font-family: 'Outfit', sans-serif;
    font-size: 13px; outline: none; cursor: pointer; appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236B665A' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 12px center;
  }
  .filter-select:focus { border-color: var(--gold); color: var(--text-1); }

  .data-panel {
    background: var(--glass); border: 1px solid var(--glass-border); border-radius: 12px;
    overflow: hidden; backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
  }
  
  .btn-outline {
    background: transparent; border: 1px solid var(--glass-border); color: var(--text-2);
    padding: 6px 14px; border-radius: 6px; font-family: 'Outfit', sans-serif; font-size: 12px;
    cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;
  }
  .btn-outline:hover { border-color: var(--gold); color: var(--gold); background: var(--gold-dim); }
  .btn-primary { background: linear-gradient(135deg, var(--gold-light), var(--gold-dark)); border: none; color: var(--bg-deep); font-weight: 600; }
  .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 15px var(--gold-glow); }
  
  table { width: 100%; border-collapse: collapse; }
  th {
    text-align: left; padding: 16px 20px; font-size: 10px; font-weight: 600;
    letter-spacing: 0.15em; text-transform: uppercase; color: var(--text-3);
    border-bottom: 1px solid var(--glass-border); background: rgba(0,0,0,0.2);
  }
  td {
    padding: 16px 20px; font-size: 14px; color: var(--text-2); font-weight: 300;
    border-bottom: 1px solid rgba(255,255,255,0.03);
  }
  tr:hover td { background: rgba(255,255,255,0.02); }
  
  .status-pill {
    padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: 600;
    letter-spacing: 0.1em; text-transform: uppercase; display: inline-block;
  }
  .status-pill.pending { background: rgba(229, 192, 123, 0.1); color: var(--gold); border: 1px solid rgba(229, 192, 123, 0.3); }

  .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(8px); z-index: 200; display: flex; align-items: flex-start; justify-content: center; padding: 20px; overflow-y: auto; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
  .modal-overlay.active { opacity: 1; pointer-events: auto; }
  .modal { background: rgba(15,15,20,0.97); border: 1px solid var(--glass-border); border-radius: 20px; padding: 0; width: 100%; max-width: 600px; margin: auto; display: flex; flex-direction: column; max-height: calc(100vh - 40px); transform: scale(0.95); transition: transform 0.3s cubic-bezier(0.16,1,0.3,1); overflow: hidden; }
  .modal-overlay.active .modal { transform: scale(1); }
  .modal-header { display: flex; align-items: center; justify-content: space-between; padding: 28px 32px 16px; border-bottom: 1px solid var(--glass-border); flex-shrink: 0; }
  .modal-body { padding: 24px 32px; overflow-y: auto; flex: 1; }
  .modal-body::-webkit-scrollbar { width: 4px; }
  .modal-body::-webkit-scrollbar-thumb { background: var(--glass-border); border-radius: 4px; }
  .modal-footer { padding: 16px 32px 28px; border-top: 1px solid var(--glass-border); background: rgba(15,15,20,0.97); flex-shrink: 0; }
  .modal-title { font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 500; color: var(--text-1); display: flex; align-items: center; gap: 10px; }
  .modal-close { background: transparent; border: 1px solid var(--glass-border); width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-3); font-size: 18px; cursor: pointer; transition: all 0.2s; }
  .modal-close:hover { background: var(--danger-dim); border-color: rgba(248,113,113,0.3); color: var(--danger); }

  .modal-actions { display: flex; gap: 12px; }

  .modal-error {
    display: none;
    align-items: center;
    gap: 10px;
    background: rgba(255, 107, 122, 0.1);
    border: 1px solid rgba(255, 107, 122, 0.35);
    border-radius: 10px;
    padding: 12px 16px;
    color: var(--danger);
    font-size: 13px;
    margin-bottom: 12px;
  }
  .modal-error.visible { display: flex; }
  @media (max-width: 600px) {
    .modal-overlay { padding: 0; align-items: flex-end; }
    .modal { max-width: 100%; border-radius: 20px 20px 0 0; max-height: 92vh; margin: 0; }
    .modal-header { padding: 20px 20px 14px; }
    .modal-body { padding: 16px 20px; }
    .modal-footer { padding: 12px 20px 20px; }
    .review-grid { grid-template-columns: 1fr; }
    .modal-actions { flex-direction: column; }
  }

  .review-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }
  .review-section { background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 12px; padding: 16px; }
  .review-label { font-size: 10px; font-weight: 600; letter-spacing: 0.15em; text-transform: uppercase; color: var(--text-3); margin-bottom: 12px; border-bottom: 1px solid var(--glass-border); padding-bottom: 8px; }
  .review-data { margin-bottom: 12px; }
  .review-data span { display: block; font-size: 11px; color: var(--text-3); margin-bottom: 2px; }
  .review-data strong { display: block; font-size: 14px; color: var(--text-1); font-weight: 400; }

  .form-group { margin-bottom: 24px; }
  .form-textarea {
    width: 100%; background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border);
    border-radius: 10px; padding: 14px 16px; color: var(--text-1); font-family: 'Outfit', sans-serif;
    font-size: 13px; outline: none; transition: border-color 0.2s; resize: vertical; min-height: 80px;
  }
  .form-textarea:focus { border-color: var(--gold); }

  .btn-approve { flex: 1; background: var(--success); border: none; padding: 14px; border-radius: 10px; color: var(--bg-deep); font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; justify-content: center; align-items: center; gap: 8px; }
  .btn-approve:hover { background: #22c55e; box-shadow: 0 4px 15px rgba(74, 222, 128, 0.3); }
  .btn-deny { flex: 1; background: transparent; border: 1px solid rgba(255, 107, 122, 0.4); padding: 14px; border-radius: 10px; color: var(--danger); font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; justify-content: center; align-items: center; gap: 8px; }
  .btn-deny:hover { background: rgba(255, 107, 122, 0.1); border-color: var(--danger); }
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
      <span class="admin-badge">Lender Console</span>
    </div>
  </div>

  <nav class="nav-menu">
    <a href="borrower_lender_dashboard.php" class="nav-link"><i class="ph ph-squares-four"></i> Dashboard</a>
    <div class="nav-section-title">Operations</div>
    <a href="#" class="nav-link active"><i class="ph ph-bell-ringing"></i> Pending Approvals <span class="nav-badge" id="pendingBadge" style="display:none; margin-left: auto; background: var(--danger); color: #fff; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 20px;">0</span></a>
    <a href="asset_management.php" class="nav-link"><i class="ph ph-stack"></i> Asset Management</a>
  </nav>

  <div class="sidebar-footer">
    <div class="admin-profile">
      <div class="admin-avatar" id="sidebarAvatar">UN</div>
      <div class="admin-info">
        <span class="admin-name" id="sidebarName">User Name</span>
        <span class="admin-role" id="sidebarRole">Lender</span>
      </div>
      <a href="javascript:void(0);" onclick="window.api.removeToken(); window.location.href='login.php'" style="margin-left: auto; color: var(--text-3); cursor: pointer;"><i class="ph ph-sign-out" style="font-size: 20px;"></i></a>
    </div>
  </div>
</aside>

<main class="main-content">
  
  <div class="header-area">
    <div class="page-title">
      <h1>Pending Approvals</h1>
      <p>Confirm borrower requests before they are sent to the administration for final review.</p>
    </div>
  </div>

  <div class="data-panel">
    <table>
      <thead>
        <tr>
          <th>Transaction ID</th>
          <th>Borrower</th>
          <th>Asset Requested</th>
          <th>Duration / Schedule</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="requestsTableBody">
        <tr>
            <td colspan="6" style="text-align: center; padding: 20px;">Loading requests...</td>
        </tr>
      </tbody>
    </table>
  </div>

</main>

<div class="modal-overlay" id="reviewModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" style="color: var(--text-1);">
        <i class="ph ph-file-text" style="color: var(--gold);"></i>
        Review Request <span id="modalTxnId" style="font-family: monospace; font-size: 20px; color: var(--text-3); margin-left: 8px;"></span>
      </div>
      <button class="modal-close" onclick="closeReviewModal()"><i class="ph ph-x"></i></button>
    </div>

    <div class="modal-body">
      <div class="review-grid">
        <div class="review-section">
          <div class="review-label">Borrower Information</div>
          <div class="review-data"><span>Name</span><strong id="modalBorrowerName">-</strong></div>
          <div class="review-data"><span>Reputation</span><strong id="modalBorrowerRating">—</strong></div>
          <div class="review-data"><span>School ID</span><strong id="modalSchoolId">-</strong></div>
          <div class="review-data"><span>Email</span><strong id="modalEmail">-</strong></div>
        </div>
        <div class="review-section">
          <div class="review-label">Fulfillment Details</div>
          <div class="review-data"><span>Asset</span><strong id="modalAsset">-</strong></div>
          <div class="review-data"><span>Schedule</span><strong id="modalSchedule">-</strong></div>
          <div class="review-data"><span>Requested Date</span><strong id="modalRequestedDate">-</strong></div>
        </div>
      </div>

      <form id="decisionForm">
        <input type="hidden" id="hiddenTxnId" value="">
        <div class="form-group" style="margin-top: 16px;">
          <span class="review-label" style="border: none; padding: 0; margin-bottom: 8px; display:block;">Lender Remarks (Optional)</span>
          <textarea class="form-textarea" id="lenderRemarks" placeholder="Add notes before confirming or denying..."></textarea>
        </div>
      </form>
    </div>

    <div class="modal-footer">
      <div class="modal-error" id="modalError">
        <i class="ph ph-warning-circle" style="font-size:18px; flex-shrink:0;"></i>
        <span id="modalErrorMsg">An error occurred. Please try again.</span>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-deny" onclick="processAction(this, 'reject')"><i class="ph ph-x-circle"></i> Deny Request</button>
        <button type="button" class="btn-approve" onclick="processAction(this, 'confirm')"><i class="ph ph-check-circle"></i> Confirm Request</button>
      </div>
    </div>
  </div>
</div>

<script>
  const glow = document.getElementById('glow');
  document.addEventListener('mousemove', (e) => {
    glow.style.setProperty('--mouse-x', e.clientX + 'px');
    glow.style.setProperty('--mouse-y', e.clientY + 'px');
  });

  document.addEventListener('DOMContentLoaded', async () => {
    let user = window.api.getUser();
    
    if (!user || !user.name) {
        try {
            const data = await window.api.authenticatedFetch('/users/profile.php');
            if (data && data.status === 'success') {
                user = {
                    id: data.profile.id,
                    name: data.profile.full_name,
                    email: data.profile.email,
                    role: data.profile.role,
                    profile_picture: data.profile.profile_picture
                };
                window.api.setUser(user);
            }
        } catch (e) { console.error('Failed to fetch profile', e); }
    }

    if (user && user.name) {
        document.getElementById('sidebarName').textContent = user.name;
        document.getElementById('sidebarRole').textContent = user.role;
        const avatarEl = document.getElementById('sidebarAvatar');
        if (avatarEl) {
          if (user.profile_picture) {
            avatarEl.innerHTML = `<img src="${user.profile_picture}" alt="Profile" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
          } else {
            const parts = String(user.name).trim().split(/\s+/).filter(Boolean);
            const initials = ((parts[0] ? parts[0][0] : 'U') + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase();
            avatarEl.textContent = initials;
          }
        }
    }
    fetchPendingRequests();
  });

  async function fetchPendingRequests() {
      try {
          const response = await window.api.authenticatedFetch('/transactions/history.php');
          if (response && response.status === 'success') {
              const tbody = document.getElementById('requestsTableBody');
              tbody.innerHTML = '';
              
              // Only requests this user must act on as the asset owner (lender), not their own borrower submissions.
              const pendingRequests = response.history.filter(t =>
                (String(t.status || '').toLowerCase() === 'pending' || String(t.status || '').toLowerCase() === 'return_lender_confirm') &&
                t.is_current_user_lender === true
              );
              
              if (pendingRequests.length === 0) {
                  tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;">No pending requests to confirm.</td></tr>';
                  if (document.getElementById('pendingBadge')) document.getElementById('pendingBadge').style.display = 'none';
                  return;
              }

              const badge = document.getElementById('pendingBadge');
              if (badge) {
                  badge.textContent = pendingRequests.length;
                  badge.style.display = 'inline-block';
              }

              window.currentRequests = pendingRequests; // Store for modal

              pendingRequests.forEach(req => {
                  const tr = document.createElement('tr');
                  
                  const isReturn = String(req.status || '').toLowerCase() === 'return_lender_confirm';
                  // Format dates
                  const dueStr = req.dates.due ? new Date(req.dates.due).toLocaleDateString() : 'N/A';
                  const br = req.borrower || {};
                  const rc = Number(br.rating_count) || 0;
                  const ra = Number(br.rating_average) || 0;
                  const repLine = rc > 0
                    ? `<br><span style="font-size: 11px; color: var(--gold); font-weight: 500;">${ra.toFixed(2)} ★ (${rc})</span>`
                    : `<br><span style="font-size: 11px; color: var(--text-3);">No ratings yet</span>`;

                  const statusDisplay = isReturn ? 'Return Review' : req.status;

                  tr.innerHTML = `
                      <td style="color: var(--text-1); font-family: monospace;">#TXN-${req.transaction_id}</td>
                      <td>${req.borrower.name} <br><span style="font-size: 11px; color: var(--text-3);">${req.borrower.school_id}</span>${repLine}</td>
                      <td style="color: var(--text-1);">${req.asset.name}</td>
                      <td style="white-space: nowrap;">
                          <span style="color: var(--gold); font-weight: 500;">Due: ${dueStr}</span>
                      </td>
                      <td><span class="status-pill pending">${statusDisplay}</span></td>
                      <td>
                          <button class="btn-outline btn-primary" onclick="openReviewModal(${req.transaction_id})">
                              <i class="ph ph-magnifying-glass-plus"></i> Review
                          </button>
                      </td>
                  `;
                  tbody.appendChild(tr);
              });
          }
      } catch (error) {
          console.error("Failed to load requests:", error);
      }
  }

  function openReviewModal(txnId) {
    const req = window.currentRequests.find(r => r.transaction_id == txnId);
    if(!req) return;

    document.getElementById('modalTxnId').textContent = '#TXN-' + txnId;
    document.getElementById('hiddenTxnId').value = txnId;
    
    document.getElementById('modalBorrowerName').textContent = req.borrower.name;
    const brc = Number(req.borrower.rating_count) || 0;
    const bra = Number(req.borrower.rating_average) || 0;
    const ratingEl = document.getElementById('modalBorrowerRating');
    if (ratingEl) {
      ratingEl.textContent = brc > 0
        ? `${bra.toFixed(2)} ★ average (${brc} rating${brc === 1 ? '' : 's'})`
        : 'No ratings yet';
    }
    document.getElementById('modalSchoolId').textContent = req.borrower.school_id;
    document.getElementById('modalEmail').textContent = req.borrower.email;
    
    document.getElementById('modalAsset').textContent = req.asset.name;
    document.getElementById('modalSchedule').textContent = req.dates.due ? new Date(req.dates.due).toLocaleString() : 'N/A';
    document.getElementById('modalRequestedDate').textContent = new Date(req.dates.requested).toLocaleString();

    const isReturn = String(req.status || '').toLowerCase() === 'return_lender_confirm';
    
    // Add photo proof section if return review
    let photoSection = document.getElementById('modalPhotoSection');
    if (!photoSection) {
      photoSection = document.createElement('div');
      photoSection.id = 'modalPhotoSection';
      photoSection.className = 'review-section';
      photoSection.style.gridColumn = '1 / -1';
      document.querySelector('.review-grid').appendChild(photoSection);
    }
    
    if (isReturn) {
      document.querySelector('.modal-title').innerHTML = `<i class="ph ph-file-text" style="color: var(--gold);"></i> Confirm Return <span id="modalTxnId" style="font-family: monospace; font-size: 20px; color: var(--text-3); margin-left: 8px;">#TXN-${txnId}</span>`;
      const photos = req.return_photos || [];
      const photosHtml = photos.length > 0 ? photos.map(p => `<img src="${p.photo_path}" style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--glass-border);cursor:pointer;" onclick="window.open('${p.photo_path}','_blank')">`).join(' ') : '<span>No photos submitted</span>';
      
      photoSection.style.display = 'block';
      photoSection.innerHTML = `
        <div class="review-label">Return Photo Proof & Details</div>
        <div class="review-data"><span>Penalty Amount Due</span><strong style="color:var(--danger);">PHP ${Number(req.penalty_amount || 0).toFixed(2)}</strong></div>
        <div class="review-data"><span>Photos</span><div style="display:flex;gap:10px;margin-top:8px;">${photosHtml}</div></div>
      `;
    } else {
      document.querySelector('.modal-title').innerHTML = `<i class="ph ph-file-text" style="color: var(--gold);"></i> Review Request <span id="modalTxnId" style="font-family: monospace; font-size: 20px; color: var(--text-3); margin-left: 8px;">#TXN-${txnId}</span>`;
      photoSection.style.display = 'none';
    }

    document.getElementById('reviewModal').classList.add('active');
  }

  function closeReviewModal() {
    document.getElementById('reviewModal').classList.remove('active');
  }

  function showModalError(msg) {
    const el = document.getElementById('modalError');
    const msgEl = document.getElementById('modalErrorMsg');
    if (el && msgEl) {
      msgEl.textContent = msg || 'An error occurred. Please try again.';
      el.classList.add('visible');
      setTimeout(() => el.classList.remove('visible'), 6000);
    }
  }

  async function processAction(btn, action) {
    const txnId = document.getElementById('hiddenTxnId').value;
    const remarks = document.getElementById('lenderRemarks').value;
    document.getElementById('modalError')?.classList.remove('visible');
    
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="ph ph-spinner-gap"></i> Processing...';
    
    const btns = document.querySelectorAll('.modal-actions button');
    btns.forEach(b => b.disabled = true);

    try {
        const req = window.currentRequests.find(r => r.transaction_id == txnId);
        const isReturn = req && String(req.status || '').toLowerCase() === 'return_lender_confirm';
        const endpoint = isReturn ? '/transactions/lender_confirm_return.php' : '/transactions/lender_confirm.php';
        
        const response = await window.api.authenticatedFetch(endpoint, {
            method: 'PUT',
            body: {
                transaction_id: txnId,
                action: action,
                remarks: remarks
            }
        });

        if (response && response.status === 'success') {
            btn.innerHTML = '<i class="ph ph-check"></i> Success';
            setTimeout(() => {
                closeReviewModal();
                btn.innerHTML = originalText;
                btns.forEach(b => b.disabled = false);
                fetchPendingRequests(); // refresh list
            }, 800);
        } else {
            showModalError(response?.message || 'Failed to process request.');
            btn.innerHTML = originalText;
            btns.forEach(b => b.disabled = false);
        }
    } catch(err) {
        console.error(err);
        showModalError(err?.message || 'Network error. Please try again.');
        btn.innerHTML = originalText;
        btns.forEach(b => b.disabled = false);
    }
  }
</script>
  <script src="../js/theme.js"></script>

</body>
</html>
