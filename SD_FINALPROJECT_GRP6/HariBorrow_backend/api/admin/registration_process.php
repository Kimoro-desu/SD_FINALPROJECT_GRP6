<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
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

$data = json_decode(file_get_contents("php://input"));
$requestId = isset($data->request_id) ? (int)$data->request_id : 0;
$action = isset($data->action) ? strtolower(trim((string)$data->action)) : '';

if ($requestId <= 0 || !in_array($action, ['approve', 'reject'], true)) {
    http_response_code(400);
    echo json_encode(["message" => "request_id and action ('approve'|'reject') are required.", "status" => "error"]);
    exit();
}

try {
    $db->beginTransaction();

    $stmt = $db->prepare("SELECT user_id, status FROM registration_requests WHERE request_id = :rid LIMIT 1 FOR UPDATE");
    $stmt->execute([':rid' => $requestId]);
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$row) {
        $db->rollBack();
        http_response_code(404);
        echo json_encode(["message" => "Registration request not found.", "status" => "error"]);
        exit();
    }

    if (strtolower(trim($row['status'])) !== 'pending') {
        $db->rollBack();
        http_response_code(409);
        echo json_encode(["message" => "Request already processed.", "status" => "error"]);
        exit();
    }

    $newStatus = $action === 'approve' ? 'Approved' : 'Rejected';
    $upd = $db->prepare("UPDATE registration_requests SET status = :s, processed_at = NOW(), processed_by = :by WHERE request_id = :rid");
    $upd->execute([
        ':s' => $newStatus,
        ':by' => (int)($decodedData['id'] ?? 0),
        ':rid' => $requestId
    ]);

    $db->commit();

    SystemLogger::log(
        $db,
        'admin',
        ($newStatus === 'Approved' ? 'Approved' : 'Rejected') . ' registration request (request_id: ' . $requestId . ', user_id: ' . (int)$row['user_id'] . ').',
        (string)($decodedData['email'] ?? null),
        $_SERVER['REMOTE_ADDR'] ?? null,
        'Success'
    );

    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Request " . strtolower($newStatus) . "."]);
} catch (\PDOException $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>

