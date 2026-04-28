<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/penalty_schema.php';

use Config\Database;
use function Utils\ensurePenaltySchema;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$database = new Database();
$db = $database->getConnection();
ensurePenaltySchema($db);

// Check if there's an availability filter provided in the URL (e.g. ?status=Available)
$statusFilter = isset($_GET['status']) ? htmlspecialchars(strip_tags($_GET['status'])) : null;

// Build query to get assets along with their lender's name
$query = "SELECT a.Asset_ID, a.Lender_ID, a.asset_name, a.asset_type, a.description, a.meetup_location, a.proposed_penalty_amount, a.daily_penalty, a.penalty_type, a.availability, a.time_created,
                 u.first_name, u.last_name 
          FROM assets a 
          LEFT JOIN users u ON a.Lender_ID = u.User_ID";

if ($statusFilter) {
    $query .= " WHERE a.availability = :status";
}

$query .= " ORDER BY a.time_created DESC";

$stmt = $db->prepare($query);

if ($statusFilter) {
    $stmt->bindParam(":status", $statusFilter);
}

try {
    $stmt->execute();
    $num = $stmt->rowCount();

    $assets_arr = array();
    
    if ($num > 0) {
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            // Reconstruct the lender's full name if available
            $first = isset($row['first_name']) ? $row['first_name'] : '';
            $last = isset($row['last_name']) ? $row['last_name'] : '';
            $lender_name = ($first !== '' || $last !== '') 
                ? trim($first . ' ' . $last) 
                : "System Default";

            array_push($assets_arr, [
                "id" => $row['Asset_ID'],
                "name" => $row['asset_name'],
                "type" => $row['asset_type'],
                "description" => $row['description'],
                "meetup_location" => $row['meetup_location'],
                "proposed_penalty_amount" => (int)($row['proposed_penalty_amount'] ?? 0),
                "daily_penalty" => (int)($row['daily_penalty'] ?? 0),
                "penalty_type" => $row['penalty_type'] ?? 'per_day',
                "status" => $row['availability'],
                "lender_id" => $row['Lender_ID'],
                "lender_name" => $lender_name,
                "created_at" => $row['time_created']
            ]);
        }
    }
    
    // Always return 200 OK so the frontend can handle empty arrays gracefully
    http_response_code(200);
    echo json_encode(["message" => "Assets fetched successfully.", "assets" => $assets_arr, "status" => "success"]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>
