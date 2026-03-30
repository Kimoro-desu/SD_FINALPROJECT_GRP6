<?php
// Headers for CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
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

$data = json_decode(file_get_contents("php://input"));

// Verify data exists
if (!empty($data->email) && !empty($data->password)) {

    // Fetch user by email
    $query = "SELECT User_ID, first_name, last_name, password_hash, user_role, plm_email 
              FROM users 
              WHERE plm_email = :email LIMIT 0,1";

    $stmt = $db->prepare($query);
    $email = htmlspecialchars(strip_tags($data->email));
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $num = $stmt->rowCount();

    if ($num > 0) {
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Verify password hash
        if (password_verify($data->password, $row['password_hash'])) {
            
            // Build claims payload for JWT
            $tokenPayload = [
                "id" => $row['User_ID'],
                "email" => $row['plm_email'],
                "role" => $row['user_role']
            ];

            // Generate JWT token
            $jwt = JwtHelper::generateToken($tokenPayload);

            http_response_code(200);
            echo json_encode([
                "message" => "Successful login.",
                "token" => $jwt,
                "user" => [
                    "id" => $row['User_ID'],
                    "name" => $row['first_name'] . " " . $row['last_name'],
                    "email" => $row['plm_email'],
                    "role" => $row['user_role']
                ],
                "status" => "success"
            ]);
        } else {
            // Password mismatch
            http_response_code(401);
            echo json_encode(["message" => "Invalid credentials.", "status" => "error"]);
        }
    } else {
        // User not found
        http_response_code(404);
        echo json_encode(["message" => "User does not exist.", "status" => "error"]);
    }
} else {
    // Missing credentials
    http_response_code(400);
    echo json_encode(["message" => "Incomplete login data.", "status" => "error"]);
}
?>
