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
if (!in_array($role, [strtolower(Database::ROLE_ADMIN), strtolower(Database::ROLE_STAFF)], true)) {
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

// Run DDL outside transaction
try { ensureUserNotificationsTable($db); } catch (\Exception $e) { error_log('ensureUserNotificationsTable: ' . $e->getMessage()); }
try { ensureAssetPhotosSchema($db); } catch (\Exception $e) { error_log('ensureAssetPhotosSchema: ' . $e->getMessage()); }

try {
    $db->beginTransaction();

    // Fetch the transaction
    $check = $db->prepare("SELECT t.transaction_id, t.asset_id, t.borrower_id, t.request_status, t.penalty_amount,
                                   a.Lender_ID, a.asset_name
                            FROM transactions t
                            JOIN assets a ON t.asset_id = a.Asset_ID
                            WHERE t.transaction_id = :tid FOR UPDATE");
    $check->bindValue(':tid', $transactionId, \PDO::PARAM_INT);
    $check->execute();
    $row = $check->fetch(\PDO::FETCH_ASSOC);

    if (!$row) {
        $db->rollBack();
        http_response_code(404);
        die(json_encode(["message" => "Transaction not found.", "status" => "error"]));
    }

    if ($row['request_status'] !== Database::STATUS_RETURN_PENDING) {
        $db->rollBack();
        http_response_code(400);
        die(json_encode(["message" => "This transaction is not pending return approval. Current status: " . $row['request_status'], "status" => "error"]));
    }

    $assetId = $row['asset_id'];
    $borrowerId = (int)$row['borrower_id'];
    $lenderId = (int)$row['Lender_ID'];
    $assetName = $row['asset_name'] ?? 'Asset';
    $penaltyAmount = (float)($row['penalty_amount'] ?? 0);

    // Fetch return photos for notification
    $photoStmt = $db->prepare("SELECT photo_path FROM return_photos WHERE transaction_id = :tid ORDER BY uploaded_at ASC LIMIT 5");
    $photoStmt->bindValue(':tid', $transactionId, \PDO::PARAM_INT);
    $photoStmt->execute();
    $photos = $photoStmt->fetchAll(\PDO::FETCH_COLUMN);
    $photoCount = count($photos);

    if ($action === 'approve') {
        // Finalize the return
        $update = $db->prepare("UPDATE transactions SET request_status = :status, rating_locked = 1 WHERE transaction_id = :tid");
        $update->execute([':status' => Database::STATUS_RETURNED, ':tid' => $transactionId]);

        // Release the asset
        $assetUpdate = $db->prepare("UPDATE assets SET availability = 'available' WHERE Asset_ID = :aid");
        $assetUpdate->execute([':aid' => $assetId]);

        // Notify borrower
        if ($borrowerId > 0) {
            $penaltyMsg = $penaltyAmount > 0
                ? ' Penalty due: PHP ' . number_format($penaltyAmount, 0) . '.'
                : ' No penalties applied.';
            pushUserNotification($db, $borrowerId, 'Return Approved ✓', 'Your return of "' . $assetName . '" has been approved by the administrator.' . $penaltyMsg, $penaltyAmount > 0 ? 'warning' : 'success', $penaltyAmount > 0 ? 'ph-warning-circle' : 'ph-check-circle');
        }

        // Notify lender with photo count
        if ($lenderId > 0) {
            $photoNote = $photoCount > 0 ? ' (' . $photoCount . ' return photo(s) submitted)' : '';
            pushUserNotification($db, $lenderId, 'Asset Returned Successfully', 'Your asset "' . $assetName . '" has been returned and verified by admin.' . $photoNote, 'success', 'ph-check-circle');
        }

        $db->commit();
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Return approved. Asset released back to catalog.",
            "penalty_amount" => $penaltyAmount
        ]);

    } else {
        // Reject — revert to Approved (borrower still has the item)
        $update = $db->prepare("UPDATE transactions SET request_status = :status, return_date = NULL WHERE transaction_id = :tid");
        $update->execute([':status' => Database::STATUS_APPROVED, ':tid' => $transactionId]);

        // Notify borrower
        if ($borrowerId > 0) {
            pushUserNotification($db, $borrowerId, 'Return Rejected', 'Your return of "' . $assetName . '" was rejected by the administrator. Please ensure you return the asset in proper condition.', 'warning', 'ph-warning-circle');
        }

        $db->commit();
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Return rejected. Transaction reverted to active."
        ]);
    }

} catch (\PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>
