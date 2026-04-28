<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
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
    die(json_encode(["message" => "Access denied. Missing or invalid token format.", "status" => "error"]));
}

$decodedData = JwtHelper::validateToken($jwt);

if (!$decodedData) {
    http_response_code(401);
    die(json_encode(["message" => "Access denied. Token expired or invalid.", "status" => "error"]));
}

// Restrict this operation to users who are Admin, Lender, or Staff
$allowed_roles = [Database::ROLE_ADMIN, Database::ROLE_LENDER, Database::ROLE_STAFF, Database::ROLE_STUDENT, Database::ROLE_FACULTY, Database::ROLE_RESEARCHER];
if (!in_array($decodedData['role'], $allowed_roles)) {
    http_response_code(403);
    die(json_encode(["message" => "Forbidden. You do not have permission to add assets.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->asset_name)) {
    try {
        $query = "INSERT INTO assets (Lender_ID, asset_name, asset_type, description, availability) 
                  VALUES (:lender_id, :asset_name, :asset_type, :description, :availability)";
        
        $stmt = $db->prepare($query);
        
        // Sanitize
        $asset_name = htmlspecialchars(strip_tags($data->asset_name));
        $asset_type = isset($data->asset_type) ? htmlspecialchars(strip_tags($data->asset_type)) : null;
        $description = isset($data->description) ? htmlspecialchars(strip_tags($data->description)) : null;
        $lender_id = $decodedData['id'];

        // Force Pending status for non-admins
        if ($decodedData['role'] !== Database::ROLE_ADMIN) {
            $availability = Database::AVAILABILITY_PENDING;
        } else {
            $availability = isset($data->availability) ? htmlspecialchars(strip_tags($data->availability)) : Database::AVAILABILITY_AVAILABLE;
        }

        // Bind
        $stmt->bindParam(":lender_id", $lender_id);
        $stmt->bindParam(":asset_name", $asset_name);
        $stmt->bindParam(":asset_type", $asset_type);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":availability", $availability);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Asset added successfully.", "status" => "success"]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to add asset.", "status" => "error"]);
        }
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete data. Asset name is required.", "status" => "error"]);
}
?>
