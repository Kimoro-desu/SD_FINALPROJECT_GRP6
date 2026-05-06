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

// Accept both JSON and multipart payloads.
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput);

$getField = function (string $key) use ($data) {
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }
    if (is_object($data) && isset($data->{$key})) {
        return $data->{$key};
    }
    return null;
};

$idNumber = $getField('idNumber');
$emailRaw = $getField('email');
$passwordRaw = $getField('password');
$firstNameRaw = $getField('firstName');
$lastNameRaw = $getField('lastName');
$roleRaw = $getField('role');
$departmentRaw = $getField('department');
$contactRaw = $getField('contact');

// Ensure the essential fields from sign_up.html are present
if (
    !empty($idNumber) &&
    !empty($emailRaw) &&
    !empty($passwordRaw) &&
    !empty($firstNameRaw) &&
    !empty($lastNameRaw) &&
    !empty($roleRaw) &&
    !empty($departmentRaw)
) {
    try {
        if (!isset($_FILES['id_picture']) || $_FILES['id_picture']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(["message" => "School ID image is required during registration.", "status" => "error"]);
            exit();
        }

        $idFile = $_FILES['id_picture'];
        $fileNameCmps = explode(".", $idFile['name']);
        $fileExtension = strtolower(end($fileNameCmps));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($fileExtension, $allowedExtensions, true)) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid ID image type. Allowed: jpg, jpeg, png, gif, webp.", "status" => "error"]);
            exit();
        }

        $uploadDir = '../../uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newIdFileName = "id_reg_" . uniqid('', true) . "." . $fileExtension;
        $destPath = $uploadDir . $newIdFileName;
        if (!move_uploaded_file($idFile['tmp_name'], $destPath)) {
            http_response_code(500);
            echo json_encode(["message" => "Unable to upload ID image.", "status" => "error"]);
            exit();
        }
        $idPhotoDbPath = "/SD_FINALPROJECT_GRP6/HariBorrow_backend/uploads/profiles/" . $newIdFileName;

        // We omit User_ID so the database auto-increments it
        $query = "INSERT INTO users 
                  (first_name, last_name, password_hash, user_role, plm_email, school_id_number, department, contact_number, id_verification_status, id_photo_url)
                  VALUES
                  (:first_name, :last_name, :password_hash, :user_role, :plm_email, :school_id, :department, :contact, :id_verification_status, :id_photo_url)";

        $stmt = $db->prepare($query);

        // Sanitize incoming data matching the frontend's JSON keys
        $first_name = htmlspecialchars(strip_tags((string)$firstNameRaw));
        $last_name = htmlspecialchars(strip_tags((string)$lastNameRaw));
        $email = htmlspecialchars(strip_tags((string)$emailRaw));
        $role = htmlspecialchars(strip_tags((string)$roleRaw));
        $school_id = htmlspecialchars(strip_tags((string)$idNumber));
        $department = htmlspecialchars(strip_tags((string)$departmentRaw));
        $contact = !empty($contactRaw) ? htmlspecialchars(strip_tags((string)$contactRaw)) : null;
        $idVerificationStatus = 'pending';

        // Hash the password securely
        $password_hash = password_hash((string)$passwordRaw, PASSWORD_BCRYPT);

        // Bind data
        $stmt->bindParam(":first_name", $first_name);
        $stmt->bindParam(":last_name", $last_name);
        $stmt->bindParam(":password_hash", $password_hash);
        $stmt->bindParam(":user_role", $role);
        $stmt->bindParam(":plm_email", $email);
        $stmt->bindParam(":school_id", $school_id);
        $stmt->bindParam(":department", $department);
        $stmt->bindParam(":contact", $contact);
        $stmt->bindParam(":id_verification_status", $idVerificationStatus);
        $stmt->bindParam(":id_photo_url", $idPhotoDbPath);

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
            if (is_file($destPath)) {
                @unlink($destPath);
            }
            http_response_code(503);
            echo json_encode(["message" => "Unable to register user.", "status" => "error"]);
        }
    } catch (PDOException $e) {
        if (isset($destPath) && is_file($destPath)) {
            @unlink($destPath);
        }
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
