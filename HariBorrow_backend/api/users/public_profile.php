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
    $stmt = $db->prepare(
        "SELECT User_ID, school_id_number, department, first_name, middle_name, last_name, user_role, plm_email, profile_picture, background_picture
         FROM users WHERE User_ID = :id LIMIT 1"
    );
    $stmt->bindValue(':id', $targetId, \PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    if (!$row) {
        http_response_code(404);
        echo json_encode(["message" => "User not found.", "status" => "error"]);
        exit();
    }

    if (strtolower(trim((string)$row['user_role'])) === 'admin') {
        http_response_code(403);
        echo json_encode(["message" => "Administrator profiles are not publicly listed.", "status" => "error"]);
        exit();
    }

    $ratingStmt = $db->prepare(
        "SELECT COUNT(*) AS rating_count, COALESCE(AVG(rating), 0) AS rating_average
         FROM transaction_ratings WHERE ratee_id = :id"
    );
    $ratingStmt->bindValue(':id', $targetId, \PDO::PARAM_INT);
    $ratingStmt->execute();
    $ratingRow = $ratingStmt->fetch(\PDO::FETCH_ASSOC) ?: ['rating_count' => 0, 'rating_average' => 0];

    http_response_code(200);
    echo json_encode([
        'message' => 'Profile loaded.',
        'profile' => [
            'id' => (int)$row['User_ID'],
            'school_id_number' => $row['school_id_number'],
            'department' => $row['department'],
            'first_name' => $row['first_name'],
            'middle_name' => $row['middle_name'],
            'last_name' => $row['last_name'],
            'full_name' => trim($row['first_name'] . ' ' . ($row['middle_name'] ?? '') . ' ' . $row['last_name']),
            'email' => $row['plm_email'],
            'role' => $row['user_role'],
            'profile_picture' => $row['profile_picture'],
            'background_picture' => $row['background_picture'],
            'rating_count' => (int)($ratingRow['rating_count'] ?? 0),
            'rating_average' => round((float)($ratingRow['rating_average'] ?? 0), 2),
        ],
        'status' => 'success',
    ]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Database error: ' . $e->getMessage(), 'status' => 'error']);
}
