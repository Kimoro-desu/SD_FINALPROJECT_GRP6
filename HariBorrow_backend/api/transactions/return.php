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
        $db->beginTransaction();

        $transaction_id = htmlspecialchars(strip_tags($data->transaction_id));
        
        // Fetch the transaction strictly locking it
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
        $borrower_id = $trans_row['borrower_id'];

        // If the item hasn't been approved yet or is already returned, ignore request
        if ($trans_row['request_status'] !== Database::STATUS_APPROVED) {
            $db->rollBack();
            http_response_code(400);
            die(json_encode(["message" => "Cannot return an item that isn't actively borrowed.", "status" => "error"]));
        }

        // Only the original borrower OR an Admin/Lender/Staff can mark an item as returned
        $allowed_roles = [Database::ROLE_ADMIN, Database::ROLE_LENDER, Database::ROLE_STAFF];
        if ($decodedData['id'] != $borrower_id && !in_array($decodedData['role'], $allowed_roles)) {
            $db->rollBack();
            http_response_code(403);
            die(json_encode(["message" => "Forbidden. You can only return your own items.", "status" => "error"]));
        }

        $new_status = Database::STATUS_RETURNED;
        $return_date = date('Y-m-d H:i:s');

        // Note: The Penalty system functionality will hook into this later to check if $return_date > due_date

        $update_trans = "UPDATE transactions SET request_status = :status, return_date = :return_date WHERE transaction_id = :tid";
        $update_stmt = $db->prepare($update_trans);
        $update_stmt->bindParam(':status', $new_status);
        $update_stmt->bindParam(':return_date', $return_date);
        $update_stmt->bindParam(':tid', $transaction_id);
        $update_stmt->execute();

        // Release the asset so others can borrow it
        $new_avail = Database::AVAILABILITY_AVAILABLE;
        $update_asset = "UPDATE assets SET availability = :avail WHERE Asset_ID = :asset_id";
        $asset_stmt = $db->prepare($update_asset);
        $asset_stmt->bindParam(':avail', $new_avail);
        $asset_stmt->bindParam(':asset_id', $asset_id);
        $asset_stmt->execute();

        $db->commit();
        http_response_code(200);
        echo json_encode(["message" => "Item marked as returned successfully.", "return_date" => $return_date, "status" => "success"]);

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
