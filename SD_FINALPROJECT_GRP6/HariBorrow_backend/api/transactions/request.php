<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/jwt_helper.php';
require_once '../../utils/asset_lifecycle_schema.php';

use Config\Database;
use Utils\JwtHelper;
use function Utils\ensureAssetLifecycleSchema;

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

if (!empty($data->asset_id)) {
    // DDL migrations BEFORE transaction to avoid implicit-commit killing it
    try {
        ensureAssetLifecycleSchema($db);
    } catch (\Exception $schemaEx) {
        error_log('ensureAssetLifecycleSchema warning: ' . $schemaEx->getMessage());
    }

    try {
        $db->beginTransaction();

        // Check if the asset exists and is strictly 'Available'
        $asset_id = htmlspecialchars(strip_tags($data->asset_id));
        $check_query = "SELECT availability, status FROM assets WHERE Asset_ID = :asset_id FOR UPDATE";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':asset_id', $asset_id);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() == 0) {
            $db->rollBack();
            http_response_code(404);
            die(json_encode(["message" => "Asset not found.", "status" => "error"]));
        }

        $asset_row = $check_stmt->fetch(\PDO::FETCH_ASSOC);
        $assetAvailability = strtolower((string)($asset_row['availability'] ?? ''));
        $assetStatus = strtolower((string)($asset_row['status'] ?? 'approved'));

        if ($assetStatus !== 'approved') {
            $db->rollBack();
            http_response_code(409);
            die(json_encode(["message" => "Asset is not yet approved for borrowing.", "status" => "error"]));
        }

        if ($assetAvailability !== 'available' && $asset_row['availability'] !== Database::AVAILABILITY_AVAILABLE) {
            $db->rollBack();
            http_response_code(409);
            die(json_encode(["message" => "Asset is not currently available for borrowing.", "status" => "error"]));
        }

        $borrowDateRaw = isset($data->borrow_date) ? trim((string)$data->borrow_date) : '';
        $returnDateRaw = isset($data->return_date) ? trim((string)$data->return_date) : '';
        if ($borrowDateRaw === '' || $returnDateRaw === '') {
            $db->rollBack();
            http_response_code(400);
            die(json_encode(["message" => "Borrow date and return date are required.", "status" => "error"]));
        }

        $borrowTs = strtotime($borrowDateRaw);
        $returnTs = strtotime($returnDateRaw);
        if ($borrowTs === false || $returnTs === false || $returnTs <= $borrowTs) {
            $db->rollBack();
            http_response_code(400);
            die(json_encode(["message" => "Invalid dates. Return date must be later than borrow date.", "status" => "error"]));
        }

        $borrowDate = date('Y-m-d H:i:s', $borrowTs);
        $returnDate = date('Y-m-d H:i:s', $returnTs);

        // Insert pending transaction with requested schedule
        $borrower_id = $decodedData['id'];
        $request_status = Database::STATUS_PENDING;

        $insert_query = "INSERT INTO transactions (asset_id, borrower_id, request_status, borrowed_at, due_date) 
                         VALUES (:asset_id, :borrower_id, :request_status, :borrowed_at, :due_date)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(':asset_id', $asset_id);
        $insert_stmt->bindParam(':borrower_id', $borrower_id);
        $insert_stmt->bindParam(':request_status', $request_status);
        $insert_stmt->bindParam(':borrowed_at', $borrowDate);
        $insert_stmt->bindParam(':due_date', $returnDate);
        $insert_stmt->execute();

        // Mark as unavailable while request is in progress
        $new_availability = 'unavailable';
        $update_query = "UPDATE assets SET availability = :avail WHERE Asset_ID = :asset_id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':avail', $new_availability);
        $update_stmt->bindParam(':asset_id', $asset_id);
        $update_stmt->execute();

        $db->commit();
        
        http_response_code(201);
        echo json_encode(["message" => "Borrow request submitted successfully. Pending approval.", "status" => "success"]);

    } catch (\Exception $e) {
        if ($db->inTransaction()) { $db->rollBack(); }
        http_response_code(500);
        echo json_encode(["message" => "Error: " . $e->getMessage(), "status" => "error"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete request data: asset_id is required.", "status" => "error"]);
}
?>
