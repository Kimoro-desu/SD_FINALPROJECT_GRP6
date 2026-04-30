<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
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

$data = json_decode(file_get_contents("php://input"));
$transactionId = isset($data->transaction_id) ? (int)$data->transaction_id : 0;
$action = isset($data->action) ? strtolower(trim((string)$data->action)) : '';
$remarks = isset($data->remarks) ? trim(strip_tags((string)$data->remarks)) : '';

if ($transactionId <= 0 || !in_array($action, ['confirm', 'reject'])) {
    http_response_code(400);
    die(json_encode(["message" => "Valid transaction_id and action ('confirm'/'reject') required.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();
try { ensureUserNotificationsTable($db); } catch (\Exception $e) {}

try {
    $db->beginTransaction();

    $check = $db->prepare("SELECT t.transaction_id, t.borrower_id, t.request_status, a.Lender_ID, a.asset_name FROM transactions t JOIN assets a ON t.asset_id = a.Asset_ID WHERE t.transaction_id = :tid FOR UPDATE");
    $check->bindValue(':tid', $transactionId, \PDO::PARAM_INT);
    $check->execute();
    $row = $check->fetch(\PDO::FETCH_ASSOC);

    if (!$row) {
        $db->rollBack();
        http_response_code(404);
        die(json_encode(["message" => "Transaction not found.", "status" => "error"]));
    }

    $role = strtolower(trim((string)($decoded['role'] ?? '')));
    $userId = (int)$decoded['id'];

    if ((int)$row['Lender_ID'] !== $userId && !in_array($role, ['admin', 'staff'])) {
        $db->rollBack();
        http_response_code(403);
        die(json_encode(["message" => "Only the asset owner can confirm this return.", "status" => "error"]));
    }

    if ($row['request_status'] !== 'return_lender_confirm') {
        $db->rollBack();
        http_response_code(400);
        die(json_encode(["message" => "Transaction is not pending lender return confirmation.", "status" => "error"]));
    }

    $borrowerId = (int)$row['borrower_id'];
    $assetName = $row['asset_name'] ?? 'Asset';

    if ($action === 'confirm') {
        $update = $db->prepare("UPDATE transactions SET request_status = 'return_pending' WHERE transaction_id = :tid");
        $update->execute([':tid' => $transactionId]);

        if ($borrowerId > 0) {
            pushUserNotification($db, $borrowerId, 'Lender Confirmed Return', 'The lender has confirmed the return of "' . $assetName . '". It is now pending admin review.', 'success', 'ph-check-circle');
        }

        $db->commit();
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Return confirmed. Forwarded to admin."]);
    } else {
        $update = $db->prepare("UPDATE transactions SET request_status = 'approved', return_date = NULL, penalty_amount = 0 WHERE transaction_id = :tid");
        $update->execute([':tid' => $transactionId]);

        if ($borrowerId > 0) {
            pushUserNotification($db, $borrowerId, 'Lender Rejected Return', 'The lender rejected your return for "' . $assetName . '". ' . ($remarks ? 'Reason: ' . $remarks : ''), 'danger', 'ph-x-circle');
        }

        $db->commit();
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Return rejected. Reverted to active loan."]);
    }
} catch (\PDOException $e) {
    if ($db->inTransaction()) { $db->rollBack(); }
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>
