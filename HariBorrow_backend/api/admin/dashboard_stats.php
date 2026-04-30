<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/jwt_helper.php';
require_once '../../utils/penalty_schema.php';

use Config\Database;
use Utils\JwtHelper;
use function Utils\ensurePenaltySchema;

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
ensurePenaltySchema($db);

try {
    // Pending Approvals
    $stmtPending = $db->prepare("SELECT COUNT(*) as c FROM transactions WHERE request_status = :status");
    $pendingStatus = Database::STATUS_PENDING;
    $stmtPending->bindParam(':status', $pendingStatus);
    $stmtPending->execute();
    $pending = (int)($stmtPending->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);

    // Active Transactions (approved and not yet returned)
    $stmtActive = $db->prepare("SELECT COUNT(*) as c FROM transactions WHERE request_status = :status AND return_date IS NULL");
    $approvedStatus = Database::STATUS_APPROVED;
    $stmtActive->bindParam(':status', $approvedStatus);
    $stmtActive->execute();
    $active = (int)($stmtActive->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);

    // Overdue Assets (past due, not returned)
    $stmtOverdue = $db->prepare("SELECT COUNT(*) as c FROM transactions WHERE request_status = :status AND return_date IS NULL AND due_date IS NOT NULL AND due_date < NOW()");
    $stmtOverdue->bindParam(':status', $approvedStatus);
    $stmtOverdue->execute();
    $overdue = (int)($stmtOverdue->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);

    // Total Registered Users
    $stmtUsers = $db->prepare("SELECT COUNT(*) as c FROM users");
    $stmtUsers->execute();
    $users = (int)($stmtUsers->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);

    // Penalties collected/assessed from returned transactions
    $stmtPenalty = $db->prepare("SELECT COALESCE(SUM(penalty_amount), 0) as c FROM transactions");
    $stmtPenalty->execute();
    $penalties = (float)($stmtPenalty->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0);

    http_response_code(200);
    echo json_encode([
        "message" => "Admin dashboard stats fetched successfully.",
        "stats" => [
            "pending_approvals" => $pending,
            "active_transactions" => $active,
            "overdue_assets" => $overdue,
            "total_users" => $users,
            "total_penalties" => $penalties
        ],
        "status" => "success"
    ]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>

