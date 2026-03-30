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
    die(json_encode(["message" => "Access denied. Missing or invalid token format.", "status" => "error"]));
}

$decodedData = JwtHelper::validateToken($jwt);

if (!$decodedData) {
    http_response_code(401);
    die(json_encode(["message" => "Access denied. Token expired or invalid.", "status" => "error"]));
}

// Restrict this operation
$allowed_roles = [Database::ROLE_ADMIN, Database::ROLE_LENDER, Database::ROLE_STAFF];
if (!in_array($decodedData['role'], $allowed_roles)) {
    http_response_code(403);
    die(json_encode(["message" => "Forbidden. You do not have permission to update assets.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// The Asset_ID must be passed in the URL, e.g. PUT /api/assets/update.php?id=1
$asset_id = isset($_GET['id']) ? htmlspecialchars(strip_tags($_GET['id'])) : null;

if (!empty($asset_id) && !empty($data)) {
    try {
        // Build dynamic query based on what is provided
        $fields = [];
        if (isset($data->asset_name)) $fields[] = "asset_name = :asset_name";
        if (isset($data->asset_type)) $fields[] = "asset_type = :asset_type";
        if (isset($data->description)) $fields[] = "description = :description";
        if (isset($data->availability)) $fields[] = "availability = :availability";

        if (count($fields) > 0) {
            $query = "UPDATE assets SET " . implode(', ', $fields) . " WHERE Asset_ID = :id";
            $stmt = $db->prepare($query);

            $stmt->bindParam(":id", $asset_id);
            if (isset($data->asset_name)) {
                $asset_name = htmlspecialchars(strip_tags($data->asset_name));
                $stmt->bindParam(":asset_name", $asset_name);
            }
            if (isset($data->asset_type)) {
                $asset_type = htmlspecialchars(strip_tags($data->asset_type));
                $stmt->bindParam(":asset_type", $asset_type);
            }
            if (isset($data->description)) {
                $description = htmlspecialchars(strip_tags($data->description));
                $stmt->bindParam(":description", $description);
            }
            if (isset($data->availability)) {
                $availability = htmlspecialchars(strip_tags($data->availability));
                $stmt->bindParam(":availability", $availability);
            }

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode(["message" => "Asset updated successfully.", "status" => "success"]);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Asset not found or no changes made.", "status" => "error"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "No valid fields to update.", "status" => "error"]);
        }
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Asset ID and valid data are required.", "status" => "error"]);
}
?>
