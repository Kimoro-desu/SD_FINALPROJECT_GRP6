<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/jwt_helper.php';
require_once '../../utils/user_notifications.php';

use Config\Database;
use Utils\JwtHelper;
use function Utils\ensureUserNotificationsTable;
use function Utils\ensureNotificationReadColumn;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$authHeader = JwtHelper::getAuthorizationHeader();

if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $jwt = $matches[1];
} else {
    http_response_code(401);
    die(json_encode(["message" => "Access denied. Valid token required.", "status" => "error"]));
}

$decodedData = JwtHelper::validateToken($jwt);
if (!$decodedData) {
    http_response_code(401);
    die(json_encode(["message" => "Token expired or invalid.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();

try {
    $notifications = [];
    $user_id = $decodedData['id'];
    $role = strtolower(trim((string)$decodedData['role']));

    // Admin/Lender operational feed:
    // - Admin/Staff: show global pending borrowing requests
    // - Lender: show requests AND return confirmations relevant to their assets
    if (in_array($role, ['admin', 'lender', 'staff'], true)) {
        $isLender = $role === 'lender';

        // 1) Borrow requests pending approval
        $pendingStatus = Database::STATUS_PENDING; // usually 'Pending'
        $query = "SELECT t.transaction_id, t.request_status, t.time_created, a.asset_name, a.Lender_ID, u.first_name, u.last_name
                  FROM transactions t
                  JOIN assets a ON t.asset_id = a.Asset_ID
                  JOIN users u ON t.borrower_id = u.User_ID
                  WHERE t.request_status = :status" . ($isLender ? " AND a.Lender_ID = :uid" : "") . "
                  ORDER BY t.time_created DESC LIMIT 10";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':status', $pendingStatus);
        if ($isLender) $stmt->bindValue(':uid', (int)$user_id, \PDO::PARAM_INT);
        $stmt->execute();

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $notifications[] = [
                "transaction_id" => $row['transaction_id'],
                "notification_id" => null,
                "title" => "New Pending Request",
                "message" => "{$row['first_name']} requested {$row['asset_name']}",
                "severity" => "warning",
                "icon_class" => "ph-clock",
                "time_ago" => $row['time_created'],
                "is_read" => true
            ];
        }

        // 2) Returns awaiting lender confirmation (lender only)
        if ($isLender) {
            $query2 = "SELECT t.transaction_id, t.request_status, t.return_date, t.time_created, a.asset_name, u.first_name, u.last_name
                       FROM transactions t
                       JOIN assets a ON t.asset_id = a.Asset_ID
                       JOIN users u ON t.borrower_id = u.User_ID
                       WHERE t.request_status = 'return_lender_confirm'
                         AND a.Lender_ID = :uid
                       ORDER BY COALESCE(t.return_date, t.time_created) DESC
                       LIMIT 10";
            $stmt2 = $db->prepare($query2);
            $stmt2->bindValue(':uid', (int)$user_id, \PDO::PARAM_INT);
            $stmt2->execute();

            while ($row = $stmt2->fetch(\PDO::FETCH_ASSOC)) {
                $notifications[] = [
                    "transaction_id" => $row['transaction_id'],
                    "notification_id" => null,
                    "title" => "Return Awaiting Your Review",
                    "message" => "{$row['first_name']} submitted a return for {$row['asset_name']}",
                    "severity" => "warning",
                    "icon_class" => "ph-clipboard-text",
                    "time_ago" => $row['return_date'] ?: $row['time_created'],
                    "is_read" => true
                ];
            }
        }
    } else {
        // Borrower notifications are handled via user_notifications table
        // (populated by pushUserNotification on approve/reject/return).
        // No synthetic transaction-scan notifications needed here.
    }

    // User-specific notifications (asset approvals/rejections, etc.)
    ensureUserNotificationsTable($db);
    ensureNotificationReadColumn($db);
    $uStmt = $db->prepare("
        SELECT notification_id, title, message, severity, icon_class, created_at, is_read
        FROM user_notifications
        WHERE user_id = :uid
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $uStmt->bindParam(':uid', $user_id);
    $uStmt->execute();
    while ($row = $uStmt->fetch(\PDO::FETCH_ASSOC)) {
        $notifications[] = [
            "transaction_id" => null,
            "notification_id" => (int)$row['notification_id'],
            "title" => $row['title'],
            "message" => $row['message'],
            "severity" => $row['severity'] ?: 'info',
            "icon_class" => $row['icon_class'] ?: 'ph-info',
            "time_ago" => $row['created_at'],
            "is_read" => (int)($row['is_read'] ?? 0) === 1
        ];
    }

    usort($notifications, function ($a, $b) {
        return strtotime((string)($b['time_ago'] ?? '')) <=> strtotime((string)($a['time_ago'] ?? ''));
    });

    http_response_code(200);
    echo json_encode(["message" => "Notifications fetched.", "notifications" => $notifications, "status" => "success"]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>
