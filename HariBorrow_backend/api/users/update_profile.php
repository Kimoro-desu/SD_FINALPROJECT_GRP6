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

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));
$user_id = $decodedData['id'];

if (empty($data->action)) {
    http_response_code(400);
    die(json_encode(["message" => "Action is required.", "status" => "error"]));
}

try {
    if ($data->action === 'profile') {
        if (empty($data->full_name) || empty($data->email)) {
            http_response_code(400);
            die(json_encode(["message" => "Full name and email are required.", "status" => "error"]));
        }

        $full_name = htmlspecialchars(strip_tags($data->full_name));
        $email = htmlspecialchars(strip_tags($data->email));
        
        $name_parts = explode(' ', $full_name, 2);
        $first_name = $name_parts[0] ?? '';
        $last_name = $name_parts[1] ?? '';

        $query = "UPDATE users SET first_name = :first_name, last_name = :last_name, plm_email = :email WHERE User_ID = :uid";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':uid', $user_id);
        
        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["message" => "Profile updated successfully.", "status" => "success"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to update profile.", "status" => "error"]);
        }

    } else if ($data->action === 'password') {
        if (empty($data->current_password) || empty($data->new_password)) {
            http_response_code(400);
            die(json_encode(["message" => "Current and new password are required.", "status" => "error"]));
        }

        $query = "SELECT password_hash FROM users WHERE User_ID = :uid";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':uid', $user_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $hashed_password = $row['password_hash'];
            
            if (password_verify($data->current_password, $hashed_password)) {
                $new_hash = password_hash($data->new_password, PASSWORD_BCRYPT);
                $update_query = "UPDATE users SET password_hash = :hash WHERE User_ID = :uid";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->bindParam(':hash', $new_hash);
                $update_stmt->bindParam(':uid', $user_id);
                
                if ($update_stmt->execute()) {
                    http_response_code(200);
                    echo json_encode(["message" => "Password updated successfully.", "status" => "success"]);
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => "Failed to update password.", "status" => "error"]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Incorrect current password.", "status" => "error"]);
            }
        } else {
            http_response_code(404);
            echo json_encode(["message" => "User not found.", "status" => "error"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Invalid action.", "status" => "error"]);
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Server error: " . $e->getMessage(), "status" => "error"]);
}
?>
