<?php
// Headers for CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and helper
require_once '../../config/database.php';
require_once '../../utils/jwt_helper.php';
require_once '../../utils/system_logger.php';

use Config\Database;
use Utils\JwtHelper;
use Utils\SystemLogger;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));
$ip = $_SERVER['REMOTE_ADDR'] ?? null;

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

            // Block login if registration is still pending (if approvals table exists)
            try {
                $check = $db->prepare("SELECT status FROM registration_requests WHERE user_id = :uid LIMIT 1");
                $uid = (int)$row['User_ID'];
                $check->bindParam(':uid', $uid);
                $check->execute();
                $reg = $check->fetch(\PDO::FETCH_ASSOC);
                if ($reg && strtolower(trim($reg['status'])) !== 'approved') {
                    http_response_code(403);
                    SystemLogger::log($db, 'security', 'Login blocked: registration not approved (User_ID: ' . $uid . ').', $row['plm_email'], $ip, 'Failed');
                    echo json_encode(["message" => "Your account is pending approval.", "status" => "error"]);
                    exit();
                }
            } catch (\Throwable $e) {
                // If the table doesn't exist yet, don't block legacy installs.
            }
            
            // Build claims payload for JWT
            $tokenPayload = [
                "id" => $row['User_ID'],
                "email" => $row['plm_email'],
                "role" => $row['user_role']
            ];

            // Generate JWT token
            $jwt = JwtHelper::generateToken($tokenPayload);

            http_response_code(200);
            SystemLogger::log($db, 'auth', 'User login successful.', $row['plm_email'], $ip, 'Success');
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
            SystemLogger::log($db, 'security', 'Failed login attempt (bad password).', $email, $ip, 'Failed');
            echo json_encode(["message" => "Invalid credentials.", "status" => "error"]);
        }
    } else {
        // User not found
        http_response_code(404);
        SystemLogger::log($db, 'security', 'Failed login attempt (user not found).', $email, $ip, 'Failed');
        echo json_encode(["message" => "User does not exist.", "status" => "error"]);
    }
} else {
    // Missing credentials
    http_response_code(400);
    echo json_encode(["message" => "Incomplete login data.", "status" => "error"]);
}
?>
