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

// Ensure only Admins or Staff (e.g. cashiers) can mark a penalty as 'Paid'
$allowed_roles = [Database::ROLE_ADMIN, Database::ROLE_STAFF];
if (!in_array($decodedData['role'], $allowed_roles)) {
    http_response_code(403);
    die(json_encode(["message" => "Forbidden. You do not have permission to clear penalties.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));
$penalty_id = isset($_GET['id']) ? htmlspecialchars(strip_tags($_GET['id'])) : null;

if (!empty($penalty_id)) {
    try {
        $query = "UPDATE penalties SET is_paid = 1 WHERE penalty_id = :pid AND is_paid = 0";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':pid', $penalty_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["message" => "Penalty marked as successfully paid.", "status" => "success"]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Penalty not found or already paid.", "status" => "error"]);
        }
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Penalty ID is required in the URL parameter.", "status" => "error"]);
}
?>
