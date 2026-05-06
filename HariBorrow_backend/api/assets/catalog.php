<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/asset_lifecycle_schema.php';
require_once '../../utils/penalty_schema.php';
require_once '../../utils/ratings_schema.php';
require_once '../../utils/asset_photos_schema.php';

use Config\Database;
use function Utils\ensureAssetLifecycleSchema;
use function Utils\ensurePenaltySchema;
use function Utils\ensureRatingsSchema;
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
    ensureRatingsSchema($db);
    ensureAssetPhotosSchema($db);

    // Auto-sync scheduled availability windows:
    // - past end time => unavailable
    // - within window => available
    // This keeps `availability` consistent for request enforcement.
    try {
        $db->exec("
            UPDATE assets
            SET availability = 'unavailable'
            WHERE LOWER(status) = 'approved'
              AND available_until IS NOT NULL
              AND available_until <= NOW()
        ");
        $db->exec("
            UPDATE assets
            SET availability = 'available'
            WHERE LOWER(status) = 'approved'
              AND (available_from IS NOT NULL OR available_until IS NOT NULL)
              AND (available_from IS NULL OR available_from <= NOW())
              AND (available_until IS NULL OR available_until > NOW())
        ");
    } catch (\Exception $syncEx) {
        // Non-fatal: catalog should still load.
        error_log('catalog availability sync warning: ' . $syncEx->getMessage());
    }

    $q = "SELECT a.Asset_ID, a.asset_name, a.description, a.asset_type, a.meetup_location, a.proposed_penalty_amount, a.daily_penalty, a.penalty_type, a.availability, a.status, a.time_created, a.asset_image,
                 a.available_from, a.available_until,
                 a.Lender_ID, u.first_name, u.last_name,
                 COALESCE(lr_stats.cnt, 0) AS lender_rating_count,
                 COALESCE(lr_stats.av, 0) AS lender_rating_average
          FROM assets a
          LEFT JOIN users u ON u.User_ID = a.Lender_ID
          LEFT JOIN (
              SELECT ratee_id,
                     COUNT(*) AS cnt,
                     AVG(rating) AS av
              FROM transaction_ratings
              GROUP BY ratee_id
          ) lr_stats ON lr_stats.ratee_id = a.Lender_ID
          WHERE LOWER(a.status) = 'approved'
            AND LOWER(a.availability) = 'available'
            AND (a.available_from IS NULL OR a.available_from <= NOW())
            AND (a.available_until IS NULL OR a.available_until > NOW())
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
            "asset_image" => $row['asset_image'] ?? null,
            "available_from" => $row['available_from'] ?? null,
            "available_until" => $row['available_until'] ?? null,
            "lender_id" => (int)$row['Lender_ID'],
            "lender_name" => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
            "lender_rating_count" => (int)($row['lender_rating_count'] ?? 0),
            "lender_rating_average" => round((float)($row['lender_rating_average'] ?? 0), 2),
            "created_at" => $row['time_created']
        ];
    }

    http_response_code(200);
    echo json_encode(["status" => "success", "assets" => $items]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>

