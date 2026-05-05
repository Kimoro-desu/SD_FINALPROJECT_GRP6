<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/jwt_helper.php';
require_once '../../utils/user_account_schema.php';
require_once '../../utils/system_logger.php';

use Config\Database;
use Utils\JwtHelper;
use Utils\SystemLogger;

use function Utils\ensureUserAccountSchema;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed.", "status" => "error"]);
    exit();
}

$authHeader = JwtHelper::getAuthorizationHeader();
if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    die(json_encode(["message" => "Access denied. Valid token required.", "status" => "error"]));
}

$decodedData = JwtHelper::validateToken($matches[1]);
if (!$decodedData) {
    http_response_code(401);
    die(json_encode(["message" => "Token expired or invalid.", "status" => "error"]));
}

$role = strtolower(trim((string)($decodedData['role'] ?? '')));
if ($role !== 'admin') {
    http_response_code(403);
    die(json_encode(["message" => "Access denied. Admin privileges required.", "status" => "error"]));
}

$data = json_decode(file_get_contents("php://input")) ?: null;
$userId = isset($data->user_id) ? (int)$data->user_id : 0;
$newStatus = isset($data->account_status) ? strtolower(trim((string)$data->account_status)) : '';
$notes = isset($data->admin_notes) ? trim((string)$data->admin_notes) : '';
$allowed = ['active', 'restricted', 'suspended'];

if ($userId <= 0 || !in_array($newStatus, $allowed, true)) {
    http_response_code(400);
    echo json_encode(["message" => "user_id and a valid account_status are required.", "status" => "error"]);
    exit();
}

if ($newStatus !== 'active' && $notes === '') {
    http_response_code(400);
    echo json_encode(["message" => "A reason/note is required when restricting or suspending an account.", "status" => "error"]);
    exit();
}

$notes = mb_substr($notes, 0, 500);
if ($newStatus === 'active') {
    $notes = '';
}

$adminId = (int)($decodedData['id'] ?? 0);
if ($userId === $adminId && $newStatus !== 'active') {
    http_response_code(400);
    echo json_encode(["message" => "You cannot restrict or suspend your own admin account.", "status" => "error"]);
    exit();
}

$database = new Database();
$db = $database->getConnection();
ensureUserAccountSchema($db);

try {
    $roleStmt = $db->prepare("SELECT user_role FROM users WHERE User_ID = :uid LIMIT 1");
    $roleStmt->bindValue(':uid', $userId, \PDO::PARAM_INT);
    $roleStmt->execute();
    $row = $roleStmt->fetch(\PDO::FETCH_ASSOC);
    if (!$row) {
        http_response_code(404);
        echo json_encode(["message" => "User not found.", "status" => "error"]);
        exit();
    }
    $targetRole = strtolower(trim((string)($row['user_role'] ?? '')));
    if ($targetRole === 'admin' && $newStatus !== 'active') {
        http_response_code(400);
        echo json_encode(["message" => "Administrative accounts cannot be restricted from this endpoint.", "status" => "error"]);
        exit();
    }

    $upd = $db->prepare(
        "UPDATE users SET account_status = :st, account_notes = :notes WHERE User_ID = :uid"
    );
    $upd->bindValue(':st', $newStatus);
    $notesParam = $notes !== '' ? $notes : null;
    $upd->bindValue(':notes', $notesParam, $notesParam === null ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
    $upd->bindValue(':uid', $userId, \PDO::PARAM_INT);
    $upd->execute();

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    SystemLogger::log(
        $db,
        'admin',
        'Updated user account_status to "' . $newStatus . '" (User_ID: ' . $userId . ').',
        (string)($decodedData['email'] ?? ''),
        $ip,
        'Success'
    );

    http_response_code(200);
    echo json_encode([
        "message" => "User status updated.",
        "user_id" => $userId,
        "account_status" => $newStatus,
        "status" => "success",
    ]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
