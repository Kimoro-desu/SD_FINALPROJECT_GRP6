<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
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

$data = json_decode(file_get_contents("php://input"));
$transaction_id = isset($_GET['tid']) ? htmlspecialchars(strip_tags($_GET['tid'])) : null;
if (empty($transaction_id) && !empty($data->transaction_id)) {
    $transaction_id = htmlspecialchars(strip_tags($data->transaction_id));
}

if (!empty($transaction_id)) {
    try {
        $check_query = "SELECT t.transaction_id, t.penalty_amount, a.Lender_ID 
                        FROM transactions t
                        JOIN assets a ON t.asset_id = a.Asset_ID
                        WHERE t.transaction_id = :tid";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':tid', $transaction_id);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() == 0) {
            http_response_code(404);
            die(json_encode(["message" => "Transaction not found.", "status" => "error"]));
        }
        
        $row = $check_stmt->fetch(PDO::FETCH_ASSOC);
        $roleLower = strtolower(trim((string)($decodedData['role'] ?? '')));
        $isElevated = in_array($roleLower, ['admin', 'staff'], true);
        
        if (!$isElevated && (int)$row['Lender_ID'] !== (int)$decodedData['id']) {
            http_response_code(403);
            die(json_encode(["message" => "Forbidden. Only the asset owner can resolve this penalty.", "status" => "error"]));
        }

        // Set penalty amount to 0
        $query = "UPDATE transactions SET penalty_amount = 0 WHERE transaction_id = :tid";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':tid', $transaction_id);
        $stmt->execute();
        
        http_response_code(200);
        echo json_encode(["message" => "Penalty resolved successfully.", "status" => "success"]);
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Transaction ID is required.", "status" => "error"]);
}
?>
