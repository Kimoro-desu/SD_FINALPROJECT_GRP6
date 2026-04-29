<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/jwt_helper.php';
require_once '../../utils/asset_lifecycle_schema.php';
require_once '../../utils/user_notifications.php';
require_once '../../utils/asset_photos_schema.php';

use Config\Database;
use Utils\JwtHelper;
use function Utils\ensureAssetLifecycleSchema;
use function Utils\pushUserNotification;
use function Utils\ensureAssetPhotosSchema;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
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

$role = strtolower(trim($decoded['role'] ?? ''));
if ($role !== 'admin') {
    http_response_code(403);
    die(json_encode(["message" => "Admin only endpoint.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

try {
    ensureAssetLifecycleSchema($db);
    try { ensureAssetPhotosSchema($db); } catch (\Exception $e) { error_log('ensureAssetPhotosSchema: ' . $e->getMessage()); }

    if ($method === 'GET') {
        $q = "SELECT a.Asset_ID, a.asset_name, a.description, a.asset_type, a.meetup_location, a.proposed_penalty_amount, a.daily_penalty, a.penalty_type, a.availability, a.status, a.time_created, a.asset_image,
                     a.Lender_ID, u.first_name, u.last_name, u.plm_email
              FROM assets a
              LEFT JOIN users u ON u.User_ID = a.Lender_ID
              WHERE LOWER(a.status) = 'pending'
              ORDER BY a.time_created ASC";
        $stmt = $db->prepare($q);
        $stmt->execute();

        $items = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $items[] = [
                "id" => (int)$row['Asset_ID'],
                "name" => $row['asset_name'],
                "description" => $row['description'],
                "type" => $row['asset_type'],
                "meetup_location" => $row['meetup_location'],
                "proposed_penalty_amount" => (int)($row['proposed_penalty_amount'] ?? 0),
                "daily_penalty" => (int)($row['daily_penalty'] ?? 0),
                "penalty_type" => $row['penalty_type'] ?? 'per_day',
                "status" => strtolower((string)$row['status']),
                "availability" => strtolower((string)$row['availability']),
                "lender_id" => (int)$row['Lender_ID'],
                "lender_name" => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                "lender_email" => $row['plm_email'],
                "created_at" => $row['time_created'],
                "asset_image" => $row['asset_image'] ?? null
            ];
        }

        http_response_code(200);
        echo json_encode(["status" => "success", "pending_assets" => $items]);
        exit();
    }

    if ($method === 'POST') {
        $payload = json_decode(file_get_contents("php://input"));
        $assetId = isset($payload->id) ? (int)$payload->id : 0;
        $nextStatus = strtolower(trim((string)($payload->status ?? 'approved')));
        if ($assetId <= 0) {
            http_response_code(400);
            die(json_encode(["message" => "Asset id is required.", "status" => "error"]));
        }
        if (!in_array($nextStatus, ['approved', 'rejected'], true)) {
            http_response_code(400);
            die(json_encode(["message" => "status must be 'approved' or 'rejected'.", "status" => "error"]));
        }

        // Fetch target asset owner so we can notify them after decision.
        $ownerQ = "SELECT Asset_ID, Lender_ID, asset_name FROM assets WHERE Asset_ID = :id AND LOWER(status) = 'pending' LIMIT 1";
        $ownerStmt = $db->prepare($ownerQ);
        $ownerStmt->bindValue(':id', $assetId, \PDO::PARAM_INT);
        $ownerStmt->execute();
        $ownerRow = $ownerStmt->fetch(\PDO::FETCH_ASSOC);
        if (!$ownerRow) {
            http_response_code(404);
            die(json_encode(["message" => "Pending asset not found.", "status" => "error"]));
        }

        $availability = $nextStatus === 'approved' ? 'available' : 'unavailable';
        $q = "UPDATE assets
              SET status = :status, availability = :availability
              WHERE Asset_ID = :id AND LOWER(status) = 'pending'";
        $stmt = $db->prepare($q);
        $stmt->bindValue(':status', $nextStatus);
        $stmt->bindValue(':availability', $availability);
        $stmt->bindValue(':id', $assetId, \PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() <= 0) {
            http_response_code(404);
            die(json_encode(["message" => "Pending asset not found.", "status" => "error"]));
        }

        // Notify uploader in borrower/lender dashboard.
        $assetName = (string)($ownerRow['asset_name'] ?? 'your asset');
        $lenderId = (int)($ownerRow['Lender_ID'] ?? 0);
        if ($lenderId > 0) {
            if ($nextStatus === 'approved') {
                pushUserNotification($db, $lenderId, 'Asset Approved', "Your uploaded asset '{$assetName}' was approved by Admin.", 'info', 'ph-check-circle');
            } else {
                pushUserNotification($db, $lenderId, 'Asset Rejected', "Your uploaded asset '{$assetName}' was rejected by Admin.", 'danger', 'ph-x-circle');
            }
        }

        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Asset status updated to {$nextStatus}."]);
        exit();
    }

    http_response_code(405);
    echo json_encode(["message" => "Method not allowed.", "status" => "error"]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>

