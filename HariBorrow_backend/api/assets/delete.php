<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/jwt_helper.php';
require_once '../../utils/asset_delete_cascade.php';

use Config\Database;
use Utils\JwtHelper;
use function Utils\deleteAssetWithDependents;

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

// Restrict this operation
$allowed_roles = [Database::ROLE_ADMIN, Database::ROLE_LENDER, Database::ROLE_STAFF, Database::ROLE_STUDENT, Database::ROLE_FACULTY, Database::ROLE_RESEARCHER];
if (!in_array($decodedData['role'], $allowed_roles)) {
    http_response_code(403);
    die(json_encode(["message" => "Forbidden. You do not have permission to delete assets.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();

$asset_id = isset($_GET['id']) ? htmlspecialchars(strip_tags($_GET['id'])) : null;

if (!empty($asset_id)) {
    try {
        $aid = (int)$asset_id;
        if ($aid <= 0) {
            http_response_code(400);
            die(json_encode(["message" => "Invalid asset ID.", "status" => "error"]));
        }
        $lenderFilter = ($decodedData['role'] !== Database::ROLE_ADMIN) ? (int)$decodedData['id'] : null;
        deleteAssetWithDependents($db, $aid, $lenderFilter);
        http_response_code(200);
        echo json_encode(["message" => "Asset deleted successfully.", "status" => "success"]);
    } catch (\RuntimeException $e) {
        $code = (int)$e->getCode();
        if ($code === 403) {
            http_response_code(403);
        } elseif ($code === 404) {
            http_response_code(404);
        } else {
            http_response_code(500);
        }
        echo json_encode(["message" => $e->getMessage(), "status" => "error"]);
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Asset ID is required for deletion.", "status" => "error"]);
}
?>
