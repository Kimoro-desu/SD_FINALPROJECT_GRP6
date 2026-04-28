<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
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

// Any authenticated user may upload assets as a lender.

$data = json_decode(file_get_contents("php://input"));
$name = isset($data->name) ? trim((string)$data->name) : '';
$description = isset($data->description) ? trim((string)$data->description) : '';
$type = isset($data->type) ? trim((string)$data->type) : 'General';
$dailyPenalty = isset($data->daily_penalty) ? (float)$data->daily_penalty : 0.0;
$meetupLocation = isset($data->meetup_location) ? trim((string)$data->meetup_location) : '';
$proposedPenalty = isset($data->proposed_penalty_amount) ? (float)$data->proposed_penalty_amount : $dailyPenalty;

if ($name === '') {
    http_response_code(400);
    die(json_encode(["message" => "Asset name is required.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();

try {
    ensureAssetLifecycleSchema($db);
    ensurePenaltySchema($db);

    $query = "INSERT INTO assets (Lender_ID, asset_name, asset_type, description, meetup_location, proposed_penalty_amount, daily_penalty, status, availability)
              VALUES (:lender_id, :asset_name, :asset_type, :description, :meetup_location, :proposed_penalty_amount, :daily_penalty, 'pending', 'unavailable')";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':lender_id', (int)$decoded['id'], \PDO::PARAM_INT);
    $stmt->bindValue(':asset_name', $name);
    $stmt->bindValue(':asset_type', $type);
    $stmt->bindValue(':description', $description !== '' ? $description : null);
    $stmt->bindValue(':meetup_location', $meetupLocation !== '' ? $meetupLocation : null);
    $stmt->bindValue(':proposed_penalty_amount', max(0, $proposedPenalty));
    $stmt->bindValue(':daily_penalty', max(0, $dailyPenalty));
    $stmt->execute();

    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Asset submitted and pending admin approval.",
        "asset_id" => (int)$db->lastInsertId()
    ]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>

