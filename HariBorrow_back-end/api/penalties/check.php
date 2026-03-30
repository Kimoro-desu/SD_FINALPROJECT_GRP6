<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/Database.php';
require_once '../../utils/JwtHelper.php';

use Config\Database;
use Utils\JwtHelper;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// A smart endpoint that can be called by a CRON job or manually by Staff
// We still protect it so only Admins/Lenders/Staff can trigger a global sweep manually
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $jwt = $matches[1];
    $decodedData = JwtHelper::validateToken($jwt);
    
    if (!$decodedData || !in_array($decodedData['role'], [Database::ROLE_ADMIN, Database::ROLE_STAFF])) {
        http_response_code(403);
        die(json_encode(["message" => "Forbidden. Only authorized personnel can run penalty checks.", "status" => "error"]));
    }
} else {
    // Note: If you want a real server cron to run this via wget/curl, 
    // you would either bypass this JWT check for local IPs (127.0.0.1) or pass a hardcoded API key instead.
    http_response_code(401);
    die(json_encode(["message" => "Access denied. Valid token required.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();

try {
    $db->beginTransaction();

    // Find any transaction that is currently overdue OR was returned late, 
    // AND does not already have a penalty recorded for it.
    $query = "SELECT t.transaction_id, t.borrower_id, t.due_date, t.return_date, a.asset_name
              FROM transactions t
              JOIN assets a ON t.asset_id = a.Asset_ID
              LEFT JOIN penalties p ON t.transaction_id = p.transaction_id
              WHERE p.penalty_id IS NULL 
              AND (
                  (t.request_status = :status_approved AND t.due_date < NOW()) 
                  OR 
                  (t.request_status = :status_returned AND t.return_date > t.due_date)
              )";

    $stmt = $db->prepare($query);
    $status_approved = Database::STATUS_APPROVED;
    $status_returned = Database::STATUS_RETURNED;
    $stmt->bindParam(':status_approved', $status_approved);
    $stmt->bindParam(':status_returned', $status_returned);
    $stmt->execute();
    
    $num = $stmt->rowCount();
    $penaltiesGenerated = 0;

    if ($num > 0) {
        // Prepare the insert statement once for optimization
        $insert_query = "INSERT INTO penalties (transaction_id, borrower_id, penalty_amount, reason, is_paid) 
                         VALUES (:tid, :bid, :amt, :rsn, 0)";
        $insert_stmt = $db->prepare($insert_query);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            // Flat fine of 100 for simplicity (can be upgraded to calculate days late later)
            $penalty_amount = 100.00;
            $reason = "Overdue return for item: " . $row['asset_name'];
            
            $insert_stmt->bindParam(':tid', $row['transaction_id']);
            $insert_stmt->bindParam(':bid', $row['borrower_id']);
            $insert_stmt->bindParam(':amt', $penalty_amount);
            $insert_stmt->bindParam(':rsn', $reason);
            $insert_stmt->execute();

            $penaltiesGenerated++;
        }
    }

    $db->commit();
    http_response_code(200);
    echo json_encode([
        "message" => "Penalty check completed.", 
        "penalties_generated" => $penaltiesGenerated, 
        "status" => "success"
    ]);

} catch (\PDOException $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>
