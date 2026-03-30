<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/jwt_helper.php';

use Config\Database;
use Utils\JwtHelper;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

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

// Only Staff, Lenders, or Admins can approve/reject
$allowed_roles = [Database::ROLE_ADMIN, Database::ROLE_LENDER, Database::ROLE_STAFF];
if (!in_array($decodedData['role'], $allowed_roles)) {
    http_response_code(403);
    die(json_encode(["message" => "Forbidden. Only authorized personnel can process requests.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->transaction_id) && !empty($data->action)) {
    try {
        $db->beginTransaction();

        $transaction_id = htmlspecialchars(strip_tags($data->transaction_id));
        $action = htmlspecialchars(strip_tags($data->action)); // 'approve' or 'reject'
        
        // Fetch the transaction to get the mapped asset_id
        $check_query = "SELECT asset_id, request_status FROM transactions WHERE transaction_id = :tid FOR UPDATE";
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
            die(json_encode(["message" => "This transaction has already been processed.", "status" => "error"]));
        }

        if ($action === 'approve') {
            // Usually, a due date is specified by the lender or system default (e.g. 24 hours later)
            $due_date = isset($data->due_date) ? htmlspecialchars(strip_tags($data->due_date)) : date('Y-m-d H:i:s', strtotime('+24 hours'));
            $new_status = Database::STATUS_APPROVED;
            $new_avail = Database::AVAILABILITY_BORROWED;

            $update_trans = "UPDATE transactions SET request_status = :status, due_date = :due_date WHERE transaction_id = :tid";
            $update_stmt = $db->prepare($update_trans);
            $update_stmt->bindParam(':status', $new_status);
            $update_stmt->bindParam(':due_date', $due_date);
            $update_stmt->bindParam(':tid', $transaction_id);
            $update_stmt->execute();

        } elseif ($action === 'reject') {
            $new_status = Database::STATUS_REJECTED;
            $new_avail = Database::AVAILABILITY_AVAILABLE; // Free it up again

            $update_trans = "UPDATE transactions SET request_status = :status WHERE transaction_id = :tid";
            $update_stmt = $db->prepare($update_trans);
            $update_stmt->bindParam(':status', $new_status);
            $update_stmt->bindParam(':tid', $transaction_id);
            $update_stmt->execute();
        } else {
            $db->rollBack();
            http_response_code(400);
            die(json_encode(["message" => "Invalid action. Use 'approve' or 'reject'.", "status" => "error"]));
        }

        // Update the asset status accordingly
        $update_asset = "UPDATE assets SET availability = :avail WHERE Asset_ID = :asset_id";
        $asset_stmt = $db->prepare($update_asset);
        $asset_stmt->bindParam(':avail', $new_avail);
        $asset_stmt->bindParam(':asset_id', $asset_id);
        $asset_stmt->execute();

        $db->commit();
        http_response_code(200);
        echo json_encode(["message" => "Transaction successfully updated to " . $new_status, "status" => "success"]);

    } catch (\PDOException $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Transaction ID and action ('approve'/'reject') are required.", "status" => "error"]);
}
?>
