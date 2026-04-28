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

    // Admin/Lender operational feed: pending requests queue
    if (in_array($role, ['admin', 'lender', 'staff'], true)) {
        $query = "SELECT t.transaction_id, t.request_status, t.time_created, a.asset_name, u.first_name, u.last_name 
                  FROM transactions t
                  JOIN assets a ON t.asset_id = a.Asset_ID
                  JOIN users u ON t.borrower_id = u.User_ID
                  WHERE t.request_status = :status
                  ORDER BY t.time_created DESC LIMIT 10";
        $stmt = $db->prepare($query);
        $status = Database::STATUS_PENDING;
        $stmt->bindParam(':status', $status);
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
    } else {
        // Borrower notifications: Recent Approved/Rejected/Returned requests
        $query = "SELECT t.transaction_id, t.request_status, t.time_created, a.asset_name 
                  FROM transactions t
                  JOIN assets a ON t.asset_id = a.Asset_ID
                  WHERE t.borrower_id = :uid AND t.request_status != :status
                  ORDER BY t.time_created DESC LIMIT 10";
        $stmt = $db->prepare($query);
        $status = Database::STATUS_PENDING;
        $stmt->bindParam(':uid', $user_id);
        $stmt->bindParam(':status', $status);
        $stmt->execute();

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $status = $row['request_status'];
            $title = ""; $message = ""; $severity = ""; $icon = "";
            
            if ($status === Database::STATUS_APPROVED) {
                $title = "Request Approved";
                $message = "You can now borrow {$row['asset_name']}";
                $severity = "info";
                $icon = "ph-check-circle";
            } elseif ($status === Database::STATUS_REJECTED) {
                $title = "Request Rejected";
                $message = "Your request for {$row['asset_name']} was denied";
                $severity = "danger";
                $icon = "ph-x-circle";
            } elseif ($status === Database::STATUS_RETURNED) {
                $title = "Asset Returned";
                $message = "{$row['asset_name']} was marked as returned";
                $severity = "info"; 
                $icon = "ph-arrow-u-down-left";
            }

            if ($title !== "") {
                $notifications[] = [
                    "transaction_id" => $row['transaction_id'],
                    "notification_id" => null,
                    "title" => $title,
                    "message" => $message,
                    "severity" => $severity,
                    "icon_class" => $icon,
                    "time_ago" => $row['time_created'],
                    "is_read" => true
                ];
            }
        }
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
