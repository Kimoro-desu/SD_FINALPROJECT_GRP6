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

$assetId = isset($_POST['asset_id']) ? (int)$_POST['asset_id'] : 0;
if ($assetId <= 0) {
    http_response_code(400);
    die(json_encode(["message" => "asset_id is required.", "status" => "error"]));
}

if (!isset($_FILES['asset_image']) || $_FILES['asset_image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    die(json_encode(["message" => "No valid image file uploaded.", "status" => "error"]));
}

$file = $_FILES['asset_image'];
$maxSize = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $maxSize) {
    http_response_code(400);
    die(json_encode(["message" => "Image must be under 5MB.", "status" => "error"]));
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$mimeType = mime_content_type($file['tmp_name']);
if (!in_array($mimeType, $allowedTypes, true)) {
    http_response_code(400);
    die(json_encode(["message" => "Only JPEG, PNG, GIF, or WebP images are allowed.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();

try {
    ensureAssetPhotosSchema($db);
} catch (\Exception $e) {
    error_log('ensureAssetPhotosSchema warning: ' . $e->getMessage());
}

// Verify asset belongs to uploader
$lenderId = (int)$decoded['id'];
$check = $db->prepare("SELECT Asset_ID FROM assets WHERE Asset_ID = :id AND Lender_ID = :uid LIMIT 1");
$check->execute([':id' => $assetId, ':uid' => $lenderId]);
if ($check->rowCount() === 0) {
    http_response_code(403);
    die(json_encode(["message" => "Asset not found or not owned by you.", "status" => "error"]));
}

// Generate unique filename and save
$ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
$filename = 'asset_' . $assetId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$uploadDir = realpath(__DIR__ . '/../../uploads/assets') . DIRECTORY_SEPARATOR;
$destination = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    http_response_code(500);
    die(json_encode(["message" => "Failed to save image file.", "status" => "error"]));
}

// Store relative URL path in DB
$relativePath = '/SD_FINALPROJECT_GRP6/HariBorrow_backend/uploads/assets/' . $filename;

$stmt = $db->prepare("UPDATE assets SET asset_image = :img WHERE Asset_ID = :id");
$stmt->execute([':img' => $relativePath, ':id' => $assetId]);

header("Content-Type: application/json; charset=UTF-8");
http_response_code(200);
echo json_encode([
    "status" => "success",
    "message" => "Asset image uploaded successfully.",
    "image_url" => $relativePath
]);
?>
