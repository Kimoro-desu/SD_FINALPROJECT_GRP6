<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/Database.php';
require_once '../../utils/JwtHelper.php';

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

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->asset_id)) {
    try {
        $db->beginTransaction();

        // Check if the asset exists and is strictly 'Available'
        $asset_id = htmlspecialchars(strip_tags($data->asset_id));
        $check_query = "SELECT availability FROM assets WHERE Asset_ID = :asset_id FOR UPDATE";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':asset_id', $asset_id);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() == 0) {
            $db->rollBack();
            http_response_code(404);
            die(json_encode(["message" => "Asset not found.", "status" => "error"]));
        }

        $asset_row = $check_stmt->fetch(\PDO::FETCH_ASSOC);
        if ($asset_row['availability'] !== Database::AVAILABILITY_AVAILABLE) {
            $db->rollBack();
            http_response_code(409);
            die(json_encode(["message" => "Asset is not currently available for borrowing.", "status" => "error"]));
        }

        // Insert pending transaction
        $borrower_id = $decodedData['id'];
        $request_status = Database::STATUS_PENDING;

        $check_query = "SELECT availability FROM assets WHERE Asset_ID = :asset_id FOR UPDATE";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':asset_id', $asset_id);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() == 0) {
            throw new \Exception("Asset does not exist.");
        }
        
        $asset_row = $check_stmt->fetch(\PDO::FETCH_ASSOC);
        if ($asset_row['availability'] !== Database::AVAILABILITY_AVAILABLE) {
            $db->rollBack();
            http_response_code(409); // 409 Conflict
            die(json_encode(["message" => "Asset is no longer available.", "status" => "error"]));
        }

        $insert_query = "INSERT INTO transactions (asset_id, borrower_id, request_status) 
                         VALUES (:asset_id, :borrower_id, :request_status)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(':asset_id', $asset_id);
        $insert_stmt->bindParam(':borrower_id', $borrower_id);
        $insert_stmt->bindParam(':request_status', $request_status);
        $insert_stmt->execute();

        // Flag the asset as Pending so no one else can request it concurrently
        $new_availability = Database::AVAILABILITY_PENDING;
        $update_query = "UPDATE assets SET availability = :avail WHERE Asset_ID = :asset_id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':avail', $new_availability);
        $update_stmt->bindParam(':asset_id', $asset_id);
        $update_stmt->execute();

        $db->commit();
        
        http_response_code(201);
        echo json_encode(["message" => "Borrow request submitted successfully. Pending approval.", "status" => "success"]);

    } catch (\PDOException $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete request data: asset_id is required.", "status" => "error"]);
}
?>
