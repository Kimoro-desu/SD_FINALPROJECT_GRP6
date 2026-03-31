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
    $allowed_roles = [Database::ROLE_ADMIN, Database::ROLE_STAFF];
    
    // If Admin/Lender/Staff, show ALL penalties unless a specific borrower is requested
    if (in_array($decodedData['role'], $allowed_roles)) {
        $filter_uid = isset($_GET['borrower_id']) ? strip_tags($_GET['borrower_id']) : null;
        
        $query = "SELECT p.penalty_id, p.transaction_id, p.borrower_id, p.penalty_amount, p.reason, p.is_paid,
                         u.first_name, u.last_name, u.school_id_number 
                  FROM penalties p
                  JOIN users u ON p.borrower_id = u.User_ID ";
        
        if ($filter_uid) {
            $query .= "WHERE p.borrower_id = :uid ";
        }
        $query .= "ORDER BY p.is_paid ASC, p.penalty_id DESC";
        
        $stmt = $db->prepare($query);
        if ($filter_uid) $stmt->bindParam(':uid', $filter_uid);
        
    } else {
        // Students/Borrowers only see their own penalties
        $query = "SELECT p.penalty_id, p.transaction_id, p.borrower_id, p.penalty_amount, p.reason, p.is_paid,
                         u.first_name, u.last_name, u.school_id_number
                  FROM penalties p
                  JOIN users u ON p.borrower_id = u.User_ID
                  WHERE p.borrower_id = :uid 
                  ORDER BY p.is_paid ASC, p.penalty_id DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':uid', $decodedData['id']);
    }

    $stmt->execute();
    $num = $stmt->rowCount();

    $penalties_arr = array();
    
    if ($num > 0) {
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            array_push($penalties_arr, [
                "penalty_id" => $row['penalty_id'],
                "transaction_id" => $row['transaction_id'],
                "borrower" => [
                    "id" => $row['borrower_id'],
                    "school_id" => $row['school_id_number'],
                    "name" => trim($row['first_name'] . ' ' . $row['last_name'])
                ],
                "amount" => floatval($row['penalty_amount']),
                "reason" => $row['reason'],
                "is_paid" => (bool)$row['is_paid']
            ]);
        }
    }
    
    http_response_code(200);
    echo json_encode(["message" => "Penalties fetched successfully.", "penalties" => $penalties_arr, "status" => "success"]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>
