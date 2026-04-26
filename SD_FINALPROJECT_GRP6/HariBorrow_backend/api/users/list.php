<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
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

$role = strtolower(trim($decodedData['role'] ?? ''));
if ($role !== 'admin') {
    http_response_code(403);
    die(json_encode(["message" => "Access denied. Admin privileges required.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();

try {
    $query = "SELECT User_ID, first_name, middle_name, last_name, user_role, plm_email, school_id_number, department
              FROM users
              ORDER BY last_name ASC, first_name ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $users = [];
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $fullName = trim($row['first_name'] . ' ' . ($row['middle_name'] ?? '') . ' ' . $row['last_name']);
        $users[] = [
            "id" => $row['User_ID'],
            "school_id" => $row['school_id_number'],
            "name" => $fullName,
            "email" => $row['plm_email'],
            "department" => $row['department'],
            "role" => $row['user_role']
        ];
    }

    http_response_code(200);
    echo json_encode([
        "message" => "Users fetched successfully.",
        "users" => $users,
        "status" => "success"
    ]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>

