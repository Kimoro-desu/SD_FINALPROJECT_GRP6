<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
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
if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    die(json_encode(["message" => "Access denied. Valid token required.", "status" => "error"]));
}

$decoded = JwtHelper::validateToken($matches[1]);
if (!$decoded) {
    http_response_code(401);
    die(json_encode(["message" => "Token expired or invalid.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();
$payload = json_decode(file_get_contents("php://input"));

try {
    ensureUserNotificationsTable($db);
    ensureNotificationReadColumn($db);

    $uid = (int)$decoded['id'];
    $notificationId = isset($payload->notification_id) ? (int)$payload->notification_id : 0;

    if ($notificationId > 0) {
        $stmt = $db->prepare("UPDATE user_notifications SET is_read = 1 WHERE notification_id = :nid AND user_id = :uid");
        $stmt->execute([':nid' => $notificationId, ':uid' => $uid]);
    } else {
        $stmt = $db->prepare("UPDATE user_notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0");
        $stmt->execute([':uid' => $uid]);
    }

    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Notification state updated."]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>

