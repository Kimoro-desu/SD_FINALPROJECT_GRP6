<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/jwt_helper.php';
require_once '../../utils/system_logger.php';

use Config\Database;
use Utils\JwtHelper;
use Utils\SystemLogger;

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

$role = strtolower(trim($decodedData['role'] ?? ''));
if ($role !== 'admin') {
    http_response_code(403);
    die(json_encode(["message" => "Access denied. Admin privileges required.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();

// Ensure tables exist
SystemLogger::log($db, 'admin', 'Viewed system logs.', (string)($decodedData['email'] ?? null), $_SERVER['REMOTE_ADDR'] ?? null, 'Success');

$q = "SELECT log_id, event_type, description, actor, ip_address, status, created_at FROM system_logs WHERE 1=1";
$params = [];

// Optional filters
if (!empty($_GET['event_type'])) {
    $q .= " AND event_type = :event_type";
    $params[':event_type'] = strtolower(trim((string)$_GET['event_type']));
}
if (!empty($_GET['search'])) {
    $q .= " AND (description LIKE :search OR actor LIKE :search OR ip_address LIKE :search)";
    $params[':search'] = '%' . (string)$_GET['search'] . '%';
}
if (!empty($_GET['start_date'])) {
    $q .= " AND DATE(created_at) >= :start_date";
    $params[':start_date'] = (string)$_GET['start_date'];
}
if (!empty($_GET['end_date'])) {
    $q .= " AND DATE(created_at) <= :end_date";
    $params[':end_date'] = (string)$_GET['end_date'];
}

$q .= " ORDER BY created_at DESC LIMIT 200";

try {
    $stmt = $db->prepare($q);
    $stmt->execute($params);
    $logs = [];
    while ($r = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $logs[] = [
            "id" => (int)$r['log_id'],
            "event_type" => $r['event_type'],
            "description" => $r['description'],
            "actor" => $r['actor'],
            "ip" => $r['ip_address'],
            "status" => $r['status'],
            "created_at" => $r['created_at']
        ];
    }
    http_response_code(200);
    echo json_encode(["status" => "success", "logs" => $logs]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>

