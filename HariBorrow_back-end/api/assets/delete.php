<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
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
    die(json_encode(["message" => "Forbidden. You do not have permission to delete assets.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();

$asset_id = isset($_GET['id']) ? htmlspecialchars(strip_tags($_GET['id'])) : null;

if (!empty($asset_id)) {
    try {
        // Delete the asset where the ID matches
        $query = "DELETE FROM assets WHERE Asset_ID = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $asset_id);

        if ($stmt->execute() && $stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["message" => "Asset deleted successfully.", "status" => "success"]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Asset not found or already deleted.", "status" => "error"]);
        }
    } catch (\PDOException $e) {
        // Prevent deletion if an asset is currently tied to open transactions
        if ($e->errorInfo[1] == 1451) {
            http_response_code(409);
            echo json_encode(["message" => "Cannot delete asset. It is currently tied to a transaction or a penalty.", "status" => "error"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
        }
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Asset ID is required for deletion.", "status" => "error"]);
}
?>
