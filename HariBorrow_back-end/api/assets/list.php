<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';

use Config\Database;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Check if there's an availability filter provided in the URL (e.g. ?status=Available)
$statusFilter = isset($_GET['status']) ? htmlspecialchars(strip_tags($_GET['status'])) : null;

// Build query to get assets along with their lender's name
$query = "SELECT a.Asset_ID, a.Lender_ID, a.asset_name, a.asset_type, a.description, a.availability, a.time_created,
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
            $lender_name = ($row['first_name'] || $row['last_name']) 
                ? trim($row['first_name'] . ' ' . $row['last_name']) 
                : "System Default";

            array_push($assets_arr, [
                "id" => $row['Asset_ID'],
                "name" => $row['asset_name'],
                "type" => $row['asset_type'],
                "description" => $row['description'],
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
