<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/jwt_helper.php';
require_once '../../utils/user_notifications.php';
require_once '../../utils/asset_photos_schema.php';

use Config\Database;
use Utils\JwtHelper;
use function Utils\ensureUserNotificationsTable;
use function Utils\pushUserNotification;
use function Utils\ensureAssetPhotosSchema;

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

// Only Admin/Staff can approve returns
$role = strtolower(trim((string)($decoded['role'] ?? '')));
if (!in_array($role, ['admin', 'staff'], true)) {
    http_response_code(403);
    die(json_encode(["message" => "Only administrators can approve returns.", "status" => "error"]));
}

$data = json_decode(file_get_contents("php://input"));
$transactionId = isset($data->transaction_id) ? (int)$data->transaction_id : 0;
$action = isset($data->action) ? strtolower(trim((string)$data->action)) : '';

if ($transactionId <= 0) {
    http_response_code(400);
    die(json_encode(["message" => "transaction_id is required.", "status" => "error"]));
}

if (!in_array($action, ['approve', 'reject'], true)) {
    http_response_code(400);
    die(json_encode(["message" => "action must be 'approve' or 'reject'.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();

try { ensureUserNotificationsTable($db); } catch (\Exception $e) {}
try { ensureAssetPhotosSchema($db); } catch (\Exception $e) {}

try {
    $db->beginTransaction();

    $check = $db->prepare("SELECT t.transaction_id, t.asset_id, t.borrower_id, t.request_status, t.penalty_amount, a.Lender_ID, a.asset_name FROM transactions t JOIN assets a ON t.asset_id = a.Asset_ID WHERE t.transaction_id = :tid FOR UPDATE");
    $check->bindValue(':tid', $transactionId, \PDO::PARAM_INT);
    $check->execute();
    $row = $check->fetch(\PDO::FETCH_ASSOC);

    if (!$row) {
        $db->rollBack();
        http_response_code(404);
        die(json_encode(["message" => "Transaction not found.", "status" => "error"]));
    }

    // FIXED: Using string instead of Database::STATUS_RETURN_PENDING
    if ($row['request_status'] !== 'return_pending') {
        $db->rollBack();
        http_response_code(400);
        die(json_encode(["message" => "This transaction is not pending return approval. Current status: " . $row['request_status'], "status" => "error"]));
    }

    $assetId = $row['asset_id'];
    $borrowerId = (int)$row['borrower_id'];
    $lenderId = (int)$row['Lender_ID'];
    $assetName = $row['asset_name'] ?? 'Asset';
    $penaltyAmount = (float)($row['penalty_amount'] ?? 0);

    if ($action === 'approve') {
        // FIXED: Using string 'returned'
        $update = $db->prepare("UPDATE transactions SET request_status = :status, rating_locked = 1 WHERE transaction_id = :tid");
        $update->execute([':status' => 'returned', ':tid' => $transactionId]);

        $assetUpdate = $db->prepare("UPDATE assets SET availability = 'available' WHERE Asset_ID = :aid");
        $assetUpdate->execute([':aid' => $assetId]);

        if ($borrowerId > 0) {
            pushUserNotification($db, $borrowerId, 'Return Approved ✓', 'Your return of "' . $assetName . '" has been approved by the administrator.', 'success', 'ph-check-circle');
        }

        $db->commit();
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Return approved. Asset released back to catalog.", "penalty_amount" => $penaltyAmount]);

    } else {
        // FIXED: Using string 'approved' to revert
        $update = $db->prepare("UPDATE transactions SET request_status = :status, return_date = NULL WHERE transaction_id = :tid");
        $update->execute([':status' => 'approved', ':tid' => $transactionId]);

        if ($borrowerId > 0) {
            pushUserNotification($db, $borrowerId, 'Return Rejected', 'Your return of "' . $assetName . '" was rejected by the administrator.', 'warning', 'ph-warning-circle');
        }

        $db->commit();
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Return rejected. Transaction reverted to active."]);
    }

} catch (\PDOException $e) {
    if ($db->inTransaction()) { $db->rollBack(); }
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>