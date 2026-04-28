<?php
require_once '../../HariBorrow_backend/config/database.php';
use Config\Database;

$database = new Database();
$db = $database->getConnection();

// Form handler for Return
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'return_asset') {
    $transaction_id = isset($_POST['transaction_id']) ? (int)$_POST['transaction_id'] : 0;
    
    if ($transaction_id > 0) {
        try {
            $db->beginTransaction();
            
            $now = date('Y-m-d H:i:s');
            // Update transaction
            $query = "UPDATE transactions SET request_status = 'Returned', return_date = :return_date WHERE transaction_id = :id AND request_status = 'Approved'";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':return_date', $now);
            $stmt->bindParam(':id', $transaction_id);
            $stmt->execute();
            
            // Get asset_id
            $queryAsset = "SELECT asset_id FROM transactions WHERE transaction_id = :id";
            $stmtAsset = $db->prepare($queryAsset);
            $stmtAsset->bindParam(':id', $transaction_id);
            $stmtAsset->execute();
            $row = $stmtAsset->fetch(\PDO::FETCH_ASSOC);
            
            if ($row) {
                // Update asset availability
                $updateAsset = "UPDATE assets SET availability = 'Available' WHERE Asset_ID = :asset_id";
                $stmtA = $db->prepare($updateAsset);
                $stmtA->bindParam(':asset_id', $row['asset_id']);
                $stmtA->execute();
            }
            
            $db->commit();
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid transaction ID']);
    }
    exit;
}

// Fetch active loans
$query = "
    SELECT 
        t.transaction_id,
        t.due_date,
        a.Asset_ID,
        a.asset_name,
        a.description
    FROM transactions t
    JOIN assets a ON t.asset_id = a.Asset_ID
    WHERE t.request_status = 'Approved'
";
$stmt = $db->prepare($query);
$stmt->execute();
$active_loans_raw = $stmt->fetchAll(\PDO::FETCH_ASSOC);

$loansForJs = [];
foreach ($active_loans_raw as $loan) {
    $dueDateStr = $loan['due_date'] ? date('M j, Y', strtotime($loan['due_date'])) : 'N/A';
    
    $isOverdue = false;
    $statusLabel = 'Active';
    $statusClass = 'active';
    
    if ($loan['due_date']) {
        $due = strtotime($loan['due_date']);
        $now = time();
        if ($now > $due) {
            $isOverdue = true;
            $statusLabel = 'Overdue';
            $statusClass = 'overdue';
        } else if ($due - $now < 86400 * 3) {
            $statusLabel = 'Due Soon';
            $statusClass = 'due-soon';
        }
    }
    
    $loansForJs[$loan['transaction_id']] = [
        'name' => htmlspecialchars($loan['asset_name'] ?? 'Unknown Asset'),
        'tag' => 'AST-' . str_pad($loan['Asset_ID'], 4, '0', STR_PAD_LEFT),
        'location' => 'Designated Location', // Adjust if you add location to DB
        'due' => $dueDateStr,
        'statusLabel' => $statusLabel,
        'statusClass' => $statusClass,
        'isOverdue' => $isOverdue
    ];
}
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
    background: radial-gradient(600px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(229, 192, 123, 0.04), transparent 50%);
    z-index: 9999; mix-blend-mode: screen; transition: background 0.1s;
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
    font-size: 14px; font-weight: 400; transition: all 0.2s; border: 1px solid transparent;
  }
  .nav-link i { font-size: 20px; color: var(--text-3); transition: color 0.2s; }
  .nav-link:hover { background: rgba(255,255,255,0.03); color: var(--text-1); }
  .nav-link.active { background: var(--gold-dim); border-color: rgba(229,192,123,0.2); color: var(--gold-light); }
  .nav-link.active i { color: var(--gold); }

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
</style>
</head>
<body>

<div class="bg-mesh"></div>
<div class="ambient-glow" id="glow"></div>

<!-- ── SIDEBAR ── -->
<aside class="sidebar">
  <div class="sidebar-header">
    <img src="../images/image_0.png" alt="HariBorrow Logo" class="nav-logo">
    <div>
      <div class="nav-title">HariBorrow</div>
      <span class="user-badge">User Portal</span>
    </div>
  </div>

  <nav class="nav-menu">
    <a href="dashboard.html" class="nav-link"><i class="ph ph-squares-four"></i> My Dashboard</a>
    <div class="nav-section-title">Transactions</div>
    <a href="UserBorrowing.html" class="nav-link"><i class="ph ph-package"></i> Borrow an Asset</a>
    <a href="#" class="nav-link active"><i class="ph ph-arrow-u-up-left"></i> Return an Asset</a>
    <a href="#" class="nav-link"><i class="ph ph-clock-counter-clockwise"></i> My Borrowing History</a>
    <a href="#" class="nav-link"><i class="ph ph-bell"></i> Notifications</a>
    <div class="nav-section-title">Account</div>
    <a href="#" class="nav-link"><i class="ph ph-user-circle"></i> My Profile</a>
    <a href="#" class="nav-link"><i class="ph ph-gear"></i> Settings</a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-profile">
      <div class="user-avatar">UN</div>
      <div class="user-info">
        <span class="user-name">User Name</span>
        <span class="user-dept">College of Engineering</span>
      </div>
      <a href="login.html" style="margin-left:auto;color:var(--text-3);cursor:pointer;">
        <i class="ph ph-sign-out" style="font-size:20px;"></i>
      </a>
    </div>
  </div>
</aside>

<!-- ── MAIN ── -->
<main class="main-content">

  <div class="header-area">
    <div class="page-title">
      <h1>Return an Asset</h1>
      <p>Select a borrowed item to return and confirm its condition.</p>
    </div>
  </div>

  <!-- STEPPER -->
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

  <!-- CARD -->
  <div class="card">

    <!-- ── STEP 1: SELECT ASSET ── -->
    <div class="step active" id="step1">
      <div class="card-label">Step 1 of 3</div>
      <div class="card-title">Active Loans</div>
      <div class="card-desc">Select the asset you wish to return. Only currently borrowed items are shown below.</div>

      <div class="loans-list">
        <?php if (empty($loansForJs)): ?>
          <div class="empty-state" style="padding: 20px; text-align: center;">
            <i class="ph ph-package" style="font-size: 32px; color: var(--text-3);"></i>
            <p style="color: var(--text-2); margin-top: 10px;">No active loans to return.</p>
          </div>
        <?php else: ?>
          <?php foreach ($loansForJs as $id => $l): ?>
            <div class="loan-card" id="loan-<?= $id ?>" onclick="selectLoan(<?= $id ?>)">
              <div class="loan-icon"><i class="ph ph-circuit-board"></i></div>
              <div class="loan-details">
                <div class="loan-name"><?= $l['name'] ?></div>
                <div class="loan-meta">
                  <span><i class="ph ph-hash"></i> <?= $l['tag'] ?></span>
                  <span><i class="ph ph-map-pin"></i> <?= $l['location'] ?></span>
                  <span><i class="ph ph-calendar-check"></i> Due: <?= $l['due'] ?></span>
                </div>
              </div>
              <span class="loan-status status-<?= $l['statusClass'] ?>"><?= $l['statusLabel'] ?></span>
              <div class="loan-select-indicator"><i class="ph-fill ph-check"></i></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="btn-group">
        <button class="btn btn-secondary" onclick="window.location.href='dashboard.html'">
          <i class="ph ph-arrow-left"></i> Back to Dashboard
        </button>
        <button class="btn btn-primary" id="proceedBtn" onclick="goToStep(2)" disabled>
          <i class="ph ph-arrow-right"></i> Proceed
        </button>
      </div>
    </div>

    <!-- ── STEP 2: RETURN DETAILS ── -->
    <div class="step" id="step2">
      <div class="card-label">Step 2 of 3</div>
      <div class="card-title">Return Details</div>
      <div class="card-desc">Confirm the condition of the asset and provide any relevant notes before submitting.</div>

      <!-- Overdue alert — shown conditionally -->
      <div class="overdue-banner hidden" id="overdueBanner">
        <i class="ph ph-warning"></i>
        <span>This asset is <strong>overdue</strong>. Please return it to the designated location immediately and notify the lab custodian. Late returns are logged in your borrowing record.</span>
      </div>

      <!-- Asset quick-summary -->
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

      <!-- Return date/time (auto-filled) -->
      <div class="form-group">
        <label class="form-label"><i class="ph ph-clock"></i> Return Date & Time</label>
        <div class="duration-display" id="returnDateTime">—</div>
      </div>

      <!-- Condition -->
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

      <!-- Notes -->
      <div class="form-group">
        <label class="form-label"><i class="ph ph-notepad"></i> Return Notes <span style="color:var(--text-3); font-weight:300; text-transform:none; letter-spacing:0;">(optional)</span></label>
        <textarea class="form-textarea" id="returnNotes" placeholder="Note any observations about the asset's condition, accessories returned, or other relevant remarks."></textarea>
      </div>

      <!-- Terms -->
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

    <!-- ── STEP 3: CONFIRMATION ── -->
    <div class="step" id="step3">
      <div class="success-icon">
        <i class="ph-fill ph-check-circle"></i>
      </div>
      <div class="card-label" style="text-align:center;">Return Logged</div>
      <div class="card-title" style="text-align:center; color:var(--success);">Asset Returned</div>
      <div class="card-desc" style="text-align:center; margin-bottom:28px;">Your return has been recorded and the asset's availability has been updated. Thank you for returning it on time.</div>

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
            <strong id="s-loan-status">—</strong>
          </div>
        </div>
      </div>

      <div class="summary-label" style="margin-bottom:8px;">Return Notes</div>
      <div class="notes-box" id="s-notes">—</div>

      <div class="btn-group" style="margin-top:0;">
        <button class="btn btn-secondary" onclick="window.location.reload()">
          <i class="ph ph-arrow-counter-clockwise"></i> Return Another
        </button>
        <button class="btn btn-primary" onclick="window.location.href='dashboard.html'">
          <i class="ph ph-squares-four"></i> Go to Dashboard
        </button>
      </div>
    </div>

  </div><!-- /card -->
</main>

<script>
  /* ── GLOW ── */
  const glow = document.getElementById('glow');
  document.addEventListener('mousemove', e => {
    glow.style.setProperty('--mouse-x', e.clientX + 'px');
    glow.style.setProperty('--mouse-y', e.clientY + 'px');
  });

  /* ── LOAN DATA ── */
  const loans = <?php echo json_encode($loansForJs); ?>;

  let selectedLoan = null;
  let selectedCondition = null;

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

  /* ── SUBMIT ── */
  function handleSubmit() {
    if (!selectedCondition) {
      alert('Please select the asset condition before submitting.');
      return;
    }
    if (!document.getElementById('terms').checked) {
      alert('Please acknowledge the return confirmation before submitting.');
      return;
    }

    const loan  = loans[selectedLoan];
    const notes = document.getElementById('returnNotes').value.trim() || 'No additional notes provided.';
    const now   = new Date().toLocaleString('en-PH', {
      month: 'long', day: 'numeric', year: 'numeric',
      hour: '2-digit', minute: '2-digit'
    });

    // Make AJAX request to PHP backend
    const formData = new FormData();
    formData.append('action', 'return_asset');
    formData.append('transaction_id', selectedLoan);
    formData.append('condition', selectedCondition);
    formData.append('notes', notes);

    fetch('asset_return.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        document.getElementById('s-name').textContent       = loan.name;
        document.getElementById('s-tag').textContent        = loan.tag;
        document.getElementById('s-location').textContent   = loan.location;
        document.getElementById('s-datetime').textContent   = now;
        document.getElementById('s-condition').textContent  = selectedCondition;
        document.getElementById('s-loan-status').textContent = loan.isOverdue ? 'Returned Late' : 'Returned On Time';
        document.getElementById('s-notes').textContent      = notes;
        
        goToStep(3);
      } else {
        alert('Error returning asset: ' + (data.error || 'Unknown error'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while submitting the return.');
    });
  }
</script>
</body>
</html>
