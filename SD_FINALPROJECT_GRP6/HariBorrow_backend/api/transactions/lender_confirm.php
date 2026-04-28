<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/jwt_helper.php';
require_once '../../utils/penalty_schema.php';
require_once '../../utils/user_notifications.php';

use Config\Database;
use Utils\JwtHelper;
use function Utils\ensurePenaltySchema;
use function Utils\ensureUserNotificationsTable;
use function Utils\pushUserNotification;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$authHeader = JwtHelper::getAuthorizationHeader();
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $jwt = $matches[1];
} else {
    http_response_code(401);
    die(json_encode(["message" => "Access denied.", "status" => "error"]));
}

$decodedData = JwtHelper::validateToken($jwt);
if (!$decodedData) {
    http_response_code(401);
    die(json_encode(["message" => "Token expired or invalid.", "status" => "error"]));
}

$allowed_roles = [Database::ROLE_ADMIN, Database::ROLE_LENDER, Database::ROLE_STAFF, Database::ROLE_STUDENT, Database::ROLE_FACULTY, Database::ROLE_RESEARCHER];
if (!in_array($decodedData['role'], $allowed_roles)) {
    http_response_code(403);
    die(json_encode(["message" => "Forbidden.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->transaction_id) && !empty($data->action)) {
    // DDL migrations BEFORE transaction
    try { ensurePenaltySchema($db); } catch (\Exception $e) { error_log($e->getMessage()); }
    try { ensureUserNotificationsTable($db); } catch (\Exception $e) { error_log($e->getMessage()); }

    try {
        $db->beginTransaction();

        $transaction_id = htmlspecialchars(strip_tags($data->transaction_id));
        $action = htmlspecialchars(strip_tags($data->action));

        $check_query = "SELECT asset_id, borrower_id, request_status FROM transactions WHERE transaction_id = :tid FOR UPDATE";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':tid', $transaction_id);
        $check_stmt->execute();

        if ($check_stmt->rowCount() == 0) {
            $db->rollBack();
            http_response_code(404);
            die(json_encode(["message" => "Transaction not found.", "status" => "error"]));
        }

        $trans_row = $check_stmt->fetch(\PDO::FETCH_ASSOC);
        $asset_id = $trans_row['asset_id'];

        if ($trans_row['request_status'] !== Database::STATUS_PENDING) {
            $db->rollBack();
            http_response_code(400);
            die(json_encode(["message" => "Only 'Pending' transactions can be confirmed.", "status" => "error"]));
        }

        if ($action === 'confirm') {
            $new_status = Database::STATUS_APPROVED;
            $borrowedAt = date('Y-m-d H:i:s');
            $dueDate = isset($data->due_date) ? htmlspecialchars(strip_tags($data->due_date)) : date('Y-m-d H:i:s', strtotime('+3 days'));
            $new_avail = 'unavailable';

            $update_trans = "UPDATE transactions SET request_status = :status, borrowed_at = :borrowed_at, due_date = :due_date WHERE transaction_id = :tid";
            $stmt = $db->prepare($update_trans);
            $stmt->bindParam(':status', $new_status);
            $stmt->bindParam(':borrowed_at', $borrowedAt);
            $stmt->bindParam(':due_date', $dueDate);
            $stmt->bindParam(':tid', $transaction_id);
            $stmt->execute();

            $asset_stmt = $db->prepare("UPDATE assets SET availability = :avail WHERE Asset_ID = :aid");
            $asset_stmt->execute([':avail' => $new_avail, ':aid' => $asset_id]);

        } elseif ($action === 'reject') {
            $new_status = Database::STATUS_REJECTED;
            $new_avail = 'available';

            $stmt = $db->prepare("UPDATE transactions SET request_status = :status WHERE transaction_id = :tid");
            $stmt->execute([':status' => $new_status, ':tid' => $transaction_id]);

            $asset_stmt = $db->prepare("UPDATE assets SET availability = :avail WHERE Asset_ID = :aid");
            $asset_stmt->execute([':avail' => $new_avail, ':aid' => $asset_id]);
        } else {
            $db->rollBack();
            http_response_code(400);
            die(json_encode(["message" => "Invalid action. Use 'confirm' or 'reject'.", "status" => "error"]));
        }

        // Single notification on success
        $borrowerId = (int)($trans_row['borrower_id'] ?? 0);
        if ($borrowerId > 0) {
            if ($new_status === Database::STATUS_APPROVED) {
                pushUserNotification($db, $borrowerId, 'Request Approved', 'Your borrowing request was approved and is now active.', 'info', 'ph-check-circle');
            } else {
                pushUserNotification($db, $borrowerId, 'Request Rejected', 'Your borrowing request was rejected by the lender.', 'danger', 'ph-x-circle');
            }
        }

        $db->commit();
        http_response_code(200);
        echo json_encode(["message" => "Transaction updated to " . $new_status, "status" => "success"]);

    } catch (\PDOException $e) {
        if ($db->inTransaction()) { $db->rollBack(); }
        http_response_code(500);
        echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Transaction ID and action required.", "status" => "error"]);
}
?>
