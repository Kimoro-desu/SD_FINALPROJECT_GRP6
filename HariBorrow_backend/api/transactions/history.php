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

$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

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
    // Admins and Staff can see all, regular borrowers only see their own
    $allowed_roles = [Database::ROLE_ADMIN, Database::ROLE_LENDER, Database::ROLE_STAFF];
    if (in_array($decodedData['role'], $allowed_roles)) {
        // Fetch all transactions
        $query = "SELECT t.transaction_id, t.asset_id, t.borrower_id, t.request_status, t.time_created as request_date, t.due_date, t.return_date, 
                         a.asset_name, u.first_name, u.last_name, u.school_id_number, u.plm_email
                  FROM transactions t
                  JOIN assets a ON t.asset_id = a.Asset_ID
                  JOIN users u ON t.borrower_id = u.User_ID
                  ORDER BY t.time_created DESC";
        $stmt = $db->prepare($query);
    } else {
        // Fetch only the borrower's transactions
        $query = "SELECT t.transaction_id, t.asset_id, t.borrower_id, t.request_status, t.time_created as request_date, t.due_date, t.return_date, 
                         a.asset_name, u.first_name, u.last_name, u.school_id_number, u.plm_email
                  FROM transactions t
                  JOIN assets a ON t.asset_id = a.Asset_ID
                  JOIN users u ON t.borrower_id = u.User_ID
                  WHERE t.borrower_id = :uid
                  ORDER BY t.time_created DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':uid', $decodedData['id']);
    }

    $stmt->execute();
    $num = $stmt->rowCount();

    $history_arr = array();
    
    if ($num > 0) {
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            array_push($history_arr, [
                "transaction_id" => $row['transaction_id'],
                "asset" => [
                    "id" => $row['asset_id'],
                    "name" => $row['asset_name']
                ],
                "borrower" => [
                    "id" => $row['borrower_id'],
                    "school_id" => $row['school_id_number'],
                    "name" => trim($row['first_name'] . ' ' . $row['last_name']),
                    "email" => $row['plm_email']
                ],
                "status" => $row['request_status'],
                "dates" => [
                    "requested" => $row['request_date'],
                    "due" => $row['due_date'],
                    "returned" => $row['return_date']
                ]
            ]);
        }
    }
    
    http_response_code(200);
    echo json_encode(["message" => "Transaction history fetched successfully.", "history" => $history_arr, "status" => "success"]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>
