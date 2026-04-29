<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/asset_lifecycle_schema.php';
require_once '../../utils/penalty_schema.php';
require_once '../../utils/asset_photos_schema.php';

use Config\Database;
use function Utils\ensureAssetLifecycleSchema;
use function Utils\ensurePenaltySchema;
use function Utils\ensureAssetPhotosSchema;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    ensureAssetLifecycleSchema($db);
    ensurePenaltySchema($db);
    try { ensureAssetPhotosSchema($db); } catch (\Exception $e) { error_log('ensureAssetPhotosSchema: ' . $e->getMessage()); }

    $q = "SELECT a.Asset_ID, a.asset_name, a.description, a.asset_type, a.meetup_location, a.proposed_penalty_amount, a.daily_penalty, a.penalty_type, a.availability, a.status, a.time_created, a.asset_image,
                 a.Lender_ID, u.first_name, u.last_name
          FROM assets a
          LEFT JOIN users u ON u.User_ID = a.Lender_ID
          WHERE LOWER(a.status) = 'approved'
            AND LOWER(a.availability) = 'available'
          ORDER BY a.time_created DESC";
    $stmt = $db->prepare($q);
    $stmt->execute();

    $items = [];
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $items[] = [
            "id" => (int)$row['Asset_ID'],
            "name" => $row['asset_name'],
            "description" => $row['description'],
            "meetup_location" => $row['meetup_location'],
            "proposed_penalty_amount" => (int)($row['proposed_penalty_amount'] ?? 0),
            "type" => $row['asset_type'],
            "daily_penalty" => (int)($row['daily_penalty'] ?? 0),
            "penalty_type" => $row['penalty_type'] ?? 'per_day',
            "status" => strtolower((string)$row['status']),
            "availability" => strtolower((string)$row['availability']),
            "lender_id" => (int)$row['Lender_ID'],
            "lender_name" => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
            "created_at" => $row['time_created'],
            "asset_image" => $row['asset_image'] ?? null
        ];
    }

    http_response_code(200);
    echo json_encode(["status" => "success", "assets" => $items]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>

