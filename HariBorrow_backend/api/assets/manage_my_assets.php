<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/jwt_helper.php';
require_once '../../utils/asset_lifecycle_schema.php';
require_once '../../utils/penalty_schema.php';

use Config\Database;
use Utils\JwtHelper;
use function Utils\ensureAssetLifecycleSchema;
use function Utils\ensurePenaltySchema;

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

// Any authenticated user may manage assets they personally uploaded.

$lenderId = (int)$decoded['id'];
$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

try {
    ensureAssetLifecycleSchema($db);
    ensurePenaltySchema($db);

    if ($method === 'GET') {
        $q = "SELECT Asset_ID, asset_name, description, meetup_location, proposed_penalty_amount, daily_penalty, penalty_type, Lender_ID, status, availability, asset_type, time_created
              FROM assets
              WHERE Lender_ID = :uid
              ORDER BY time_created DESC";
        $stmt = $db->prepare($q);
        $stmt->bindValue(':uid', $lenderId, \PDO::PARAM_INT);
        $stmt->execute();

        $items = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $items[] = [
                "id" => (int)$row['Asset_ID'],
                "name" => $row['asset_name'],
                "description" => $row['description'],
                "meetup_location" => $row['meetup_location'],
                "proposed_penalty_amount" => (int)($row['proposed_penalty_amount'] ?? 0),
                "daily_penalty" => (int)($row['daily_penalty'] ?? 0),
                "penalty_type" => $row['penalty_type'] ?? 'per_day',
                "lender_id" => (int)$row['Lender_ID'],
                "status" => strtolower((string)$row['status']),
                "availability" => strtolower((string)$row['availability']),
                "type" => $row['asset_type'],
                "created_at" => $row['time_created']
            ];
        }

        http_response_code(200);
        echo json_encode(["status" => "success", "assets" => $items]);
        exit();
    }

    $payload = json_decode(file_get_contents("php://input"));
    $assetId = isset($payload->id) ? (int)$payload->id : 0;
    if ($assetId <= 0) {
        http_response_code(400);
        die(json_encode(["message" => "Asset id is required.", "status" => "error"]));
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        $availability = strtolower(trim((string)($payload->availability ?? '')));
        $hasAvailability = $availability !== '';
        $hasPenalty = isset($payload->daily_penalty);
        $hasProposedPenalty = isset($payload->proposed_penalty_amount);
        $hasMeetupLocation = property_exists($payload, 'meetup_location');
        $hasPenaltyType = isset($payload->penalty_type) && in_array($payload->penalty_type, ['per_day', 'per_hour'], true);
        $dailyPenalty = $hasPenalty ? max(0, (int)$payload->daily_penalty) : 0;
        $proposedPenalty = $hasProposedPenalty ? max(0, (int)$payload->proposed_penalty_amount) : 0;
        $meetupLocation = $hasMeetupLocation ? trim((string)$payload->meetup_location) : '';
        $penaltyType = $hasPenaltyType ? $payload->penalty_type : 'per_day';

        if (!$hasAvailability && !$hasPenalty && !$hasProposedPenalty && !$hasMeetupLocation && !$hasPenaltyType) {
            http_response_code(400);
            die(json_encode(["message" => "Provide at least one field to update (availability, daily_penalty, penalty_type, proposed_penalty_amount, meetup_location).", "status" => "error"]));
        }
        if ($hasAvailability && !in_array($availability, ['available', 'unavailable'], true)) {
            http_response_code(400);
            die(json_encode(["message" => "availability must be 'available' or 'unavailable'.", "status" => "error"]));
        }

        $setParts = [];
        $params = [':id' => $assetId, ':uid' => $lenderId];
        if ($hasAvailability) {
            $setParts[] = "availability = :availability";
            $params[':availability'] = $availability;
        }
        if ($hasPenalty) {
            $setParts[] = "daily_penalty = :daily_penalty";
            $params[':daily_penalty'] = $dailyPenalty;
        }
        if ($hasProposedPenalty) {
            $setParts[] = "proposed_penalty_amount = :proposed_penalty_amount";
            $params[':proposed_penalty_amount'] = $proposedPenalty;
        }
        if ($hasMeetupLocation) {
            $setParts[] = "meetup_location = :meetup_location";
            $params[':meetup_location'] = $meetupLocation !== '' ? $meetupLocation : null;
        }
        if ($hasPenaltyType) {
            $setParts[] = "penalty_type = :penalty_type";
            $params[':penalty_type'] = $penaltyType;
        }

        $q = "UPDATE assets SET " . implode(', ', $setParts) . " WHERE Asset_ID = :id AND Lender_ID = :uid";
        $stmt = $db->prepare($q);
        $stmt->execute($params);

        if ($stmt->rowCount() <= 0) {
            http_response_code(404);
            die(json_encode(["message" => "Asset not found or not owned by user.", "status" => "error"]));
        }

        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Asset updated."]);
        exit();
    }

    if ($method === 'DELETE') {
        $q = "DELETE FROM assets WHERE Asset_ID = :id AND Lender_ID = :uid";
        $stmt = $db->prepare($q);
        $stmt->bindValue(':id', $assetId, \PDO::PARAM_INT);
        $stmt->bindValue(':uid', $lenderId, \PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() <= 0) {
            http_response_code(404);
            die(json_encode(["message" => "Asset not found or not owned by user.", "status" => "error"]));
        }

        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Asset deleted."]);
        exit();
    }

    http_response_code(405);
    echo json_encode(["message" => "Method not allowed.", "status" => "error"]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>

