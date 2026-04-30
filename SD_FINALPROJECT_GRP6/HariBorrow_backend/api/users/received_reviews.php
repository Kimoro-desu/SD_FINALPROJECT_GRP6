<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/jwt_helper.php';
require_once '../../utils/ratings_schema.php';

use Config\Database;
use Utils\JwtHelper;
use function Utils\ensureRatingsSchema;

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

$targetId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($targetId <= 0) {
    http_response_code(400);
    die(json_encode(["message" => "user_id is required.", "status" => "error"]));
}

$database = new Database();
$db = $database->getConnection();
ensureRatingsSchema($db);

try {
    $roleStmt = $db->prepare("SELECT user_role FROM users WHERE User_ID = :id LIMIT 1");
    $roleStmt->bindValue(':id', $targetId, \PDO::PARAM_INT);
    $roleStmt->execute();
    $roleRow = $roleStmt->fetch(\PDO::FETCH_ASSOC);
    if (!$roleRow) {
        http_response_code(404);
        echo json_encode(["message" => "User not found.", "status" => "error"]);
        exit();
    }
    if (strtolower(trim((string)$roleRow['user_role'])) === 'admin') {
        http_response_code(403);
        echo json_encode(["message" => "Reviews are not available for this account.", "status" => "error"]);
        exit();
    }

    $sql = "SELECT tr.rating_id, tr.transaction_id, tr.rating, tr.review_text, tr.created_at,
                   r.first_name, r.last_name
            FROM transaction_ratings tr
            JOIN users r ON r.User_ID = tr.rater_id
            WHERE tr.ratee_id = :uid
            ORDER BY tr.created_at DESC
            LIMIT 100";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':uid', $targetId, \PDO::PARAM_INT);
    $stmt->execute();

    $reviews = [];
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $reviews[] = [
            'rating_id' => (int)$row['rating_id'],
            'transaction_id' => (int)$row['transaction_id'],
            'rating' => (int)$row['rating'],
            'review_text' => $row['review_text'],
            'created_at' => $row['created_at'],
            'rater_name' => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: 'User',
        ];
    }

    http_response_code(200);
    echo json_encode([
        'message' => 'Reviews loaded.',
        'reviews' => $reviews,
        'status' => 'success',
    ]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Database error: ' . $e->getMessage(), 'status' => 'error']);
}
