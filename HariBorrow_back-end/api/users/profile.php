<?php
// Headers for CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and helper
require_once '../../config/Database.php';
require_once '../../utils/JwtHelper.php';

use Config\Database;
use Utils\JwtHelper;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get the Authorization header
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

// Validate JWT presence and format (Bearer <token>)
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $jwt = $matches[1];
} else {
    http_response_code(401);
    echo json_encode(["message" => "Access denied. Token missing or invalid format.", "status" => "error"]);
    exit();
}

// Read the decoded JWT payload
$decodedData = JwtHelper::validateToken($jwt);

// If token is valid and not expired
if ($decodedData) {
    try {
        // We fetch the latest user info straight from DB just in case fields have changed
        $query = "SELECT User_ID, first_name, middle_name, last_name, user_role, plm_email 
                  FROM users 
                  WHERE User_ID = :id LIMIT 0,1";

        $stmt = $db->prepare($query);
        $userId = $decodedData['id'];
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode([
                "message" => "Profile successfully retrieved.",
                "profile" => [
                    "id" => $row['User_ID'],
                    "first_name" => $row['first_name'],
                    "middle_name" => $row['middle_name'],
                    "last_name" => $row['last_name'],
                    "full_name" => trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']),
                    "email" => $row['plm_email'],
                    "role" => $row['user_role']
                ],
                "status" => "success"
            ]);
        } else {
            // Token is valid but user was dropped from database
            http_response_code(404);
            echo json_encode(["message" => "User record not found in database.", "status" => "error"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
    }
} else {
    // Token is expired, invalid signature, or forged
    http_response_code(401);
    echo json_encode(["message" => "Access denied. Token is expired or invalid.", "status" => "error", "expired" => true]);
}
?>
