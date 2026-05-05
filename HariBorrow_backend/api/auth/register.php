<?php
// Headers for CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database config
require_once '../../config/database.php';
require_once '../../utils/system_logger.php';

use Config\Database;
use Utils\SystemLogger;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Ensure the essential fields from sign_up.html are present
if (
    !empty($data->idNumber) && 
    !empty($data->email) &&
    !empty($data->password) &&
    !empty($data->firstName) &&
    !empty($data->lastName) &&
    !empty($data->role) &&
    !empty($data->department)
) {
    try {
        // We omit User_ID so the database auto-increments it
        $query = "INSERT INTO users 
                  (first_name, last_name, password_hash, user_role, plm_email, school_id_number, department, contact_number)
                  VALUES
                  (:first_name, :last_name, :password_hash, :user_role, :plm_email, :school_id, :department, :contact)";

        $stmt = $db->prepare($query);

        // Sanitize incoming data matching the frontend's JSON keys
        $first_name = htmlspecialchars(strip_tags($data->firstName));
        $last_name = htmlspecialchars(strip_tags($data->lastName));
        $email = htmlspecialchars(strip_tags($data->email));
        $role = htmlspecialchars(strip_tags($data->role)); 
        $school_id = htmlspecialchars(strip_tags($data->idNumber));
        $department = htmlspecialchars(strip_tags($data->department));
        $contact = isset($data->contact) ? htmlspecialchars(strip_tags($data->contact)) : null;

        // Hash the password securely
        $password_hash = password_hash($data->password, PASSWORD_BCRYPT);

        // Bind data
        $stmt->bindParam(":first_name", $first_name);
        $stmt->bindParam(":last_name", $last_name);
        $stmt->bindParam(":password_hash", $password_hash);
        $stmt->bindParam(":user_role", $role);
        $stmt->bindParam(":plm_email", $email);
        $stmt->bindParam(":school_id", $school_id);
        $stmt->bindParam(":department", $department);
        $stmt->bindParam(":contact", $contact);

        // Execute query
        if ($stmt->execute()) {
            $newUserId = (int)$db->lastInsertId();
            if ($newUserId > 0) {
                SystemLogger::ensureRegistrationRequest($db, $newUserId);
                SystemLogger::log(
                    $db,
                    'admin',
                    'New user registered and queued for approval (User_ID: ' . $newUserId . ').',
                    $email,
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    'Success'
                );
            }
            http_response_code(201);
            echo json_encode(["message" => "User was successfully registered and is pending approval.", "status" => "success"]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to register user.", "status" => "error"]);
        }
    } catch (PDOException $e) {
        // Catch duplicate entry errors (e.g., if User_ID already exists)
        if ($e->errorInfo[1] == 1062) {
            http_response_code(409);
            echo json_encode(["message" => "A user with this Student/Employee Number or Email already exists.", "status" => "error"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
        }
    }
} else {
    // Data is incomplete
    http_response_code(400);
    echo json_encode(["message" => "Unable to register. Incomplete data.", "status" => "error"]);
}
?>
