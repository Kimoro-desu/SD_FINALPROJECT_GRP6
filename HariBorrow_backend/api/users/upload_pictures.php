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

use Config\Database;
use Utils\JwtHelper;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get the Authorization header
$authHeader = JwtHelper::getAuthorizationHeader();

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

if (!$decodedData) {
    http_response_code(401);
    echo json_encode(["message" => "Access denied. Token is expired or invalid.", "status" => "error", "expired" => true]);
    exit();
}

$userId = $decodedData['id'];
$uploadDir = '../../uploads/profiles/';

// Ensure directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$response = ["status" => "success", "message" => "Upload processed."];
$updates = [];
$params = [];

// Handle Profile Picture
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
    $fileName = $_FILES['profile_picture']['name'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($fileExtension, $allowedExtensions)) {
        $newFileName = "profile_" . $userId . "_" . time() . "." . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Save the relative URL so the frontend can display it easily
            $dbPath = "/SD_FINALPROJECT_GRP6/HariBorrow_backend/uploads/profiles/" . $newFileName;
            $updates[] = "profile_picture = :profile_picture";
            $params[':profile_picture'] = $dbPath;
            $response['profile_picture'] = $dbPath;
        } else {
            $response['message'] = 'Error moving the uploaded file.';
            $response['status'] = 'error';
        }
    } else {
        $response['message'] = 'Upload failed. Allowed file types: ' . implode(',', $allowedExtensions);
        $response['status'] = 'error';
    }
}

// Handle Background Picture
if (isset($_FILES['background_picture']) && $_FILES['background_picture']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['background_picture']['tmp_name'];
    $fileName = $_FILES['background_picture']['name'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($fileExtension, $allowedExtensions)) {
        $newFileName = "bg_" . $userId . "_" . time() . "." . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Save the relative URL so the frontend can display it easily
            $dbPath = "/SD_FINALPROJECT_GRP6/HariBorrow_backend/uploads/profiles/" . $newFileName;
            $updates[] = "background_picture = :background_picture";
            $params[':background_picture'] = $dbPath;
            $response['background_picture'] = $dbPath;
        } else {
            $response['message'] = 'Error moving the uploaded file.';
            $response['status'] = 'error';
        }
    } else {
        $response['message'] = 'Upload failed. Allowed file types: ' . implode(',', $allowedExtensions);
        $response['status'] = 'error';
    }
}

if (!empty($updates)) {
    try {
        $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE User_ID = :id";
        $stmt = $db->prepare($query);
        
        $params[':id'] = $userId;
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }

        if ($stmt->execute()) {
            http_response_code(200);
            $response['message'] = 'Pictures updated successfully.';
            echo json_encode($response);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Unable to update pictures in database.", "status" => "error"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
    }
} else {
    // If no valid file was uploaded but no hard error occurred
    if ($response['status'] === 'error') {
        http_response_code(400);
    } else {
        $response['message'] = 'No files uploaded.';
    }
    echo json_encode($response);
}
?>
