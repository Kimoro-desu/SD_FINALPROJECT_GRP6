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
    die(json_encode(["message" => "Access denied. Valid token required.", "status" => "error"]));
}

$decodedData = JwtHelper::validateToken($jwt);
if (!$decodedData) {
    http_response_code(401);
    die(json_encode(["message" => "Token expired or invalid.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->transaction_id)) {
    try {
        ensurePenaltySchema($db);
        $db->beginTransaction();

        $transaction_id = htmlspecialchars(strip_tags($data->transaction_id));
        
        // Fetch the transaction strictly locking it
        $check_query = "SELECT asset_id, borrower_id, request_status, due_date FROM transactions WHERE transaction_id = :tid FOR UPDATE";
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
        $borrower_id = $trans_row['borrower_id'];

        // If the item hasn't been approved yet or is already returned, ignore request
        if ($trans_row['request_status'] !== Database::STATUS_APPROVED) {
            $db->rollBack();
            http_response_code(400);
            die(json_encode(["message" => "Cannot return an item that isn't actively borrowed.", "status" => "error"]));
        }

        // Only the original borrower OR an Admin/Lender/Staff can mark an item as returned
        $allowed_roles = [Database::ROLE_ADMIN, Database::ROLE_LENDER, Database::ROLE_STAFF, Database::ROLE_STUDENT, Database::ROLE_FACULTY, Database::ROLE_RESEARCHER];
        if ($decodedData['id'] != $borrower_id && !in_array($decodedData['role'], $allowed_roles)) {
            $db->rollBack();
            http_response_code(403);
            die(json_encode(["message" => "Forbidden. You can only return your own items.", "status" => "error"]));
        }

        $new_status = Database::STATUS_RETURNED;
        $return_date = date('Y-m-d H:i:s');
        $due_date = $trans_row['due_date'] ?? null;

        // Penalty = overdue days * daily_penalty configured by lender on asset.
        $penaltyAmount = 0.0;
        if (!empty($due_date)) {
            $dueTs = strtotime($due_date);
            $returnTs = strtotime($return_date);
            if ($dueTs !== false && $returnTs !== false && $returnTs > $dueTs) {
                $secondsLate = $returnTs - $dueTs;
                $daysLate = (int)ceil($secondsLate / 86400);
                $penaltyQ = "SELECT daily_penalty FROM assets WHERE Asset_ID = :asset_id LIMIT 1";
                $penaltyStmt = $db->prepare($penaltyQ);
                $penaltyStmt->bindParam(':asset_id', $asset_id);
                $penaltyStmt->execute();
                $dailyPenalty = (float)($penaltyStmt->fetch(\PDO::FETCH_ASSOC)['daily_penalty'] ?? 0);
                $penaltyAmount = max(0, $daysLate * $dailyPenalty);
            }
        }

        $update_trans = "UPDATE transactions
                         SET request_status = :status, return_date = :return_date, penalty_amount = :penalty_amount
                         WHERE transaction_id = :tid";
        $update_stmt = $db->prepare($update_trans);
        $update_stmt->bindParam(':status', $new_status);
        $update_stmt->bindParam(':return_date', $return_date);
        $update_stmt->bindValue(':penalty_amount', $penaltyAmount);
        $update_stmt->bindParam(':tid', $transaction_id);
        $update_stmt->execute();

        // Release the asset so others can borrow it
        $new_avail = Database::AVAILABILITY_AVAILABLE;
        $update_asset = "UPDATE assets SET availability = :avail WHERE Asset_ID = :asset_id";
        $asset_stmt = $db->prepare($update_asset);
        $asset_stmt->bindParam(':avail', $new_avail);
        $asset_stmt->bindParam(':asset_id', $asset_id);
        $asset_stmt->execute();

        if ((int)$borrower_id > 0) {
            if ($penaltyAmount > 0) {
                pushUserNotification($db, (int)$borrower_id, 'Return Processed with Penalty', 'Your return was completed. Penalty due: PHP ' . number_format($penaltyAmount, 2) . '.', 'warning', 'ph-warning-circle');
            } else {
                pushUserNotification($db, (int)$borrower_id, 'Return Completed', 'Your borrowing was returned successfully with no penalty.', 'info', 'ph-check-circle');
            }
        }

        $db->commit();
        http_response_code(200);
        echo json_encode([
            "message" => "Item marked as returned successfully.",
            "return_date" => $return_date,
            "penalty_amount" => $penaltyAmount,
            "status" => "success"
        ]);

    } catch (\PDOException $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Transaction ID is required to return an item.", "status" => "error"]);
}
?>
