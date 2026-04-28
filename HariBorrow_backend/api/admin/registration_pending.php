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
SystemLogger::log($db, 'admin', 'Viewed registration approvals queue.', (string)($decodedData['email'] ?? null), $_SERVER['REMOTE_ADDR'] ?? null, 'Success');

try {
    $q = "SELECT rr.request_id, rr.status, rr.requested_at,
                 u.User_ID, u.first_name, u.middle_name, u.last_name, u.user_role, u.plm_email, u.school_id_number, u.department, u.contact_number
          FROM registration_requests rr
          JOIN users u ON u.User_ID = rr.user_id
          WHERE rr.status = 'Pending'
          ORDER BY rr.requested_at ASC";
    $stmt = $db->prepare($q);
    $stmt->execute();

    $rows = [];
    while ($r = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $rows[] = [
            "request_id" => (int)$r['request_id'],
            "requested_at" => $r['requested_at'],
            "user" => [
                "id" => (int)$r['User_ID'],
                "school_id" => $r['school_id_number'],
                "name" => trim($r['first_name'] . ' ' . ($r['middle_name'] ?? '') . ' ' . $r['last_name']),
                "role" => $r['user_role'],
                "department" => $r['department'],
                "email" => $r['plm_email'],
                "contact" => $r['contact_number']
            ]
        ];
    }

    http_response_code(200);
    echo json_encode(["status" => "success", "pending" => $rows]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>

