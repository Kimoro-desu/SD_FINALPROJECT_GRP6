<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/jwt_helper.php';

use Config\Database;
use Utils\JwtHelper;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$authHeader = JwtHelper::getAuthorizationHeader();

if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $jwt = $matches[1];
} else {
    http_response_code(401);
    die(json_encode(["message" => "Access denied. Valid token required.", "status" => "error"]));
}

$decodedData = JwtHelper::validateToken($jwt);
if (!$decodedData) {
    http_response_code(401);
    die(json_encode(["message" => "Token expired or invalid.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();

try {
    $active_borrowings = 0;
    $pending_requests = 0;

    $user_id = $decodedData['id'];
    $role = $decodedData['role'];

    // If user is borrower, count their active borrowings (Approved and not yet returned)
    $query = "SELECT COUNT(*) as active_count FROM transactions 
              WHERE borrower_id = :uid AND request_status = :status AND return_date IS NULL";
    $stmt = $db->prepare($query);
    $status = Database::STATUS_APPROVED;
    $stmt->bindParam(':uid', $user_id);
    $stmt->bindParam(':status', $status);
    $stmt->execute();
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    $active_borrowings = $row['active_count'];

    // If user is admin/staff/lender, count pending requests waiting for approval
    $allowed_roles = [Database::ROLE_ADMIN, Database::ROLE_LENDER, Database::ROLE_STAFF, Database::ROLE_STUDENT, Database::ROLE_FACULTY, Database::ROLE_RESEARCHER];
    if (in_array($role, $allowed_roles)) {
        $query2 = "SELECT COUNT(*) as pending_count FROM transactions 
                   WHERE request_status = :status";
        $stmt2 = $db->prepare($query2);
        $status2 = Database::STATUS_PENDING;
        $stmt2->bindParam(':status', $status2);
        $stmt2->execute();
        $row2 = $stmt2->fetch(\PDO::FETCH_ASSOC);
        $pending_requests = $row2['pending_count'];
    }

    http_response_code(200);
    echo json_encode([
        "message" => "Dashboard stats fetched successfully.",
        "stats" => [
            "active_borrowings" => $active_borrowings,
            "pending_requests" => $pending_requests
        ],
        "status" => "success"
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>
