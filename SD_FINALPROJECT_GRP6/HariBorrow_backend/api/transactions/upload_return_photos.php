<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/jwt_helper.php';
require_once '../../utils/asset_photos_schema.php';

use Config\Database;
use Utils\JwtHelper;
use function Utils\ensureAssetPhotosSchema;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(["message" => "Method not allowed.", "status" => "error"]));
}

$authHeader = JwtHelper::getAuthorizationHeader();
if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    die(json_encode(["message" => "Access denied. Valid token required.", "status" => "error"]));
}

$decoded = JwtHelper::validateToken($matches[1]);
if (!$decoded) {
    http_response_code(401);
    die(json_encode(["message" => "Token expired or invalid.", "status" => "error"]));
}

$transactionId = isset($_POST['transaction_id']) ? (int)$_POST['transaction_id'] : 0;
if ($transactionId <= 0) {
    http_response_code(400);
    die(json_encode(["message" => "transaction_id is required.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();

try {
    ensureAssetPhotosSchema($db);
} catch (\Exception $e) {
    error_log('ensureAssetPhotosSchema warning: ' . $e->getMessage());
}

// Verify the transaction belongs to the borrower and is either Approved or Returned
$userId = (int)$decoded['id'];
$check = $db->prepare("SELECT transaction_id, borrower_id, request_status FROM transactions WHERE transaction_id = :tid LIMIT 1");
$check->execute([':tid' => $transactionId]);
$txRow = $check->fetch(\PDO::FETCH_ASSOC);

if (!$txRow) {
    http_response_code(404);
    die(json_encode(["message" => "Transaction not found.", "status" => "error"]));
}

if ((int)$txRow['borrower_id'] !== $userId) {
    http_response_code(403);
    die(json_encode(["message" => "You can only upload photos for your own transactions.", "status" => "error"]));
}

// Accept up to 5 photos
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB per file
$uploadDir = realpath(__DIR__ . '/../../uploads/return_photos') . DIRECTORY_SEPARATOR;
$savedPaths = [];

// Handle both single and multiple file uploads
$fileKeys = [];
if (isset($_FILES['return_photos'])) {
    // Multiple files under one key
    if (is_array($_FILES['return_photos']['name'])) {
        for ($i = 0; $i < count($_FILES['return_photos']['name']); $i++) {
            if ($_FILES['return_photos']['error'][$i] === UPLOAD_ERR_OK) {
                $fileKeys[] = [
                    'tmp_name' => $_FILES['return_photos']['tmp_name'][$i],
                    'name' => $_FILES['return_photos']['name'][$i],
                    'size' => $_FILES['return_photos']['size'][$i],
                ];
            }
        }
    } else {
        // Single file
        if ($_FILES['return_photos']['error'] === UPLOAD_ERR_OK) {
            $fileKeys[] = [
                'tmp_name' => $_FILES['return_photos']['tmp_name'],
                'name' => $_FILES['return_photos']['name'],
                'size' => $_FILES['return_photos']['size'],
            ];
        }
    }
}

if (empty($fileKeys)) {
    http_response_code(400);
    die(json_encode(["message" => "No valid image files uploaded.", "status" => "error"]));
}

if (count($fileKeys) > 5) {
    http_response_code(400);
    die(json_encode(["message" => "Maximum 5 photos allowed per return.", "status" => "error"]));
}

foreach ($fileKeys as $f) {
    if ($f['size'] > $maxSize) {
        http_response_code(400);
        die(json_encode(["message" => "Each image must be under 5MB.", "status" => "error"]));
    }
    $mimeType = mime_content_type($f['tmp_name']);
    if (!in_array($mimeType, $allowedTypes, true)) {
        http_response_code(400);
        die(json_encode(["message" => "Only JPEG, PNG, GIF, or WebP images are allowed.", "status" => "error"]));
    }

    $ext = pathinfo($f['name'], PATHINFO_EXTENSION) ?: 'jpg';
    $filename = 'return_' . $transactionId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($f['tmp_name'], $destination)) {
        http_response_code(500);
        die(json_encode(["message" => "Failed to save image file.", "status" => "error"]));
    }

    $relativePath = '/SD_FINALPROJECT_GRP6/HariBorrow_backend/uploads/return_photos/' . $filename;

    $stmt = $db->prepare("INSERT INTO return_photos (transaction_id, photo_path) VALUES (:tid, :path)");
    $stmt->execute([':tid' => $transactionId, ':path' => $relativePath]);

    $savedPaths[] = $relativePath;
}

header("Content-Type: application/json; charset=UTF-8");
http_response_code(200);
echo json_encode([
    "status" => "success",
    "message" => count($savedPaths) . " photo(s) uploaded successfully.",
    "photos" => $savedPaths
]);
?>
