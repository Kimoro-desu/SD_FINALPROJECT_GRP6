<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/jwt_helper.php';
require_once '../../utils/user_notifications.php';

use Config\Database;
use Utils\JwtHelper;
use function Utils\ensureUserNotificationsTable;
use function Utils\pushUserNotification;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// This endpoint is called periodically by the frontend to check
// for upcoming due dates and send notifications. Requires auth.
$authHeader = JwtHelper::getAuthorizationHeader();
if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    die(json_encode(["message" => "Access denied.", "status" => "error"]));
}
$decoded = JwtHelper::validateToken($matches[1]);
if (!$decoded) {
    http_response_code(401);
    die(json_encode(["message" => "Token expired or invalid.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();
try { ensureUserNotificationsTable($db); } catch (\Exception $e) { /* ignored */ }

try {
    $now = time();
    $userId = (int)$decoded['id'];
    
    // Find active loans for this user that are due within 24 hours or overdue
    $query = "SELECT t.transaction_id, t.due_date, t.borrower_id,
                     a.asset_name, a.Lender_ID
              FROM transactions t
              JOIN assets a ON t.asset_id = a.Asset_ID
              WHERE t.request_status = :status
                AND (t.borrower_id = :uid OR a.Lender_ID = :uid)
                AND t.due_date IS NOT NULL";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':status', Database::STATUS_APPROVED);
    $stmt->bindValue(':uid', $userId, \PDO::PARAM_INT);
    $stmt->execute();
    
    $notified = 0;
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $dueTs = strtotime($row['due_date']);
        if ($dueTs === false) continue;
        
        $hoursUntilDue = ($dueTs - $now) / 3600;
        $assetName = $row['asset_name'] ?? 'Asset';
        $txId = (int)$row['transaction_id'];
        $borrowerId = (int)$row['borrower_id'];
        $lenderId = (int)$row['Lender_ID'];
        
        // Check if we already sent a notification for this transaction today
        // to avoid spamming
        $checkSql = "SELECT COUNT(*) FROM user_notifications
                     WHERE user_id = :uid
                       AND title LIKE :pattern
                       AND created_at > DATE_SUB(NOW(), INTERVAL 12 HOUR)";
        
        if ($hoursUntilDue < 0) {
            // OVERDUE
            $daysOverdue = (int)ceil(abs($hoursUntilDue) / 24);
            
            // Notify borrower
            if ($userId === $borrowerId) {
                $checkStmt = $db->prepare($checkSql);
                $checkStmt->execute([':uid' => $borrowerId, ':pattern' => '%Overdue%TXN-' . $txId . '%']);
                if ((int)$checkStmt->fetchColumn() === 0) {
                    pushUserNotification($db, $borrowerId,
                        "⚠️ Overdue: TXN-{$txId}",
                        "Your borrowed item \"{$assetName}\" is overdue by {$daysOverdue} day(s). Please return it immediately to avoid additional penalties.",
                        'warning', 'ph-warning-circle');
                    $notified++;
                }
            }
            
            // Notify lender
            if ($userId === $lenderId) {
                $checkStmt = $db->prepare($checkSql);
                $checkStmt->execute([':uid' => $lenderId, ':pattern' => '%Overdue%TXN-' . $txId . '%']);
                if ((int)$checkStmt->fetchColumn() === 0) {
                    pushUserNotification($db, $lenderId,
                        "⚠️ Overdue: TXN-{$txId}",
                        "Your asset \"{$assetName}\" is overdue by {$daysOverdue} day(s). The borrower has been notified.",
                        'warning', 'ph-warning-circle');
                    $notified++;
                }
            }
        } elseif ($hoursUntilDue <= 24 && $hoursUntilDue > 0) {
            // DUE SOON (within 24 hours)
            $hoursLeft = max(1, (int)ceil($hoursUntilDue));
            
            if ($userId === $borrowerId) {
                $checkStmt = $db->prepare($checkSql);
                $checkStmt->execute([':uid' => $borrowerId, ':pattern' => '%Due Soon%TXN-' . $txId . '%']);
                if ((int)$checkStmt->fetchColumn() === 0) {
                    pushUserNotification($db, $borrowerId,
                        "⏰ Due Soon: TXN-{$txId}",
                        "Your borrowed item \"{$assetName}\" is due in approximately {$hoursLeft} hour(s). Please prepare to return it on time.",
                        'info', 'ph-clock');
                    $notified++;
                }
            }
            
            if ($userId === $lenderId) {
                $checkStmt = $db->prepare($checkSql);
                $checkStmt->execute([':uid' => $lenderId, ':pattern' => '%Due Soon%TXN-' . $txId . '%']);
                if ((int)$checkStmt->fetchColumn() === 0) {
                    pushUserNotification($db, $lenderId,
                        "⏰ Due Soon: TXN-{$txId}",
                        "Your asset \"{$assetName}\" is due to be returned in approximately {$hoursLeft} hour(s).",
                        'info', 'ph-clock');
                    $notified++;
                }
            }
        }
    }
    
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Due date check completed.",
        "notifications_sent" => $notified
    ]);
    
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>
