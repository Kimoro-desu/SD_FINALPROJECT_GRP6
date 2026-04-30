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
if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    die(json_encode(["message" => "Access denied. Valid token required.", "status" => "error"]));
}

$decoded = JwtHelper::validateToken($matches[1]);
if (!$decoded) {
    http_response_code(401);
    die(json_encode(["message" => "Token expired or invalid.", "status" => "error"]));
}

$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
if ($q === '' || mb_strlen($q) < 2) {
    http_response_code(200);
    echo json_encode(["message" => "OK", "users" => [], "status" => "success"]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$qClean = str_replace(['%', '_'], '', $q);
$needleLike = '%' . $qClean . '%';

try {
    $sql = "SELECT User_ID, school_id_number, first_name, middle_name, last_name, user_role, plm_email, profile_picture,
                   department
            FROM users
            WHERE LOWER(TRIM(user_role)) <> 'admin'
              AND (
                    LOWER(CONCAT_WS(' ', first_name, COALESCE(middle_name,''), last_name)) LIKE :n1
                 OR LOWER(plm_email) LIKE :n2
                 OR LOWER(COALESCE(school_id_number,'')) LIKE :n3
              )
            ORDER BY last_name ASC, first_name ASC
            LIMIT 20";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':n1', $needleLike, \PDO::PARAM_STR);
    $stmt->bindValue(':n2', $needleLike, \PDO::PARAM_STR);
    $stmt->bindValue(':n3', $needleLike, \PDO::PARAM_STR);
    $stmt->execute();

    $users = [];
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $full = trim($row['first_name'] . ' ' . ($row['middle_name'] ?? '') . ' ' . $row['last_name']);
        $users[] = [
            'id' => (int)$row['User_ID'],
            'name' => $full,
            'email' => $row['plm_email'],
            'school_id' => $row['school_id_number'],
            'role' => $row['user_role'],
            'department' => $row['department'],
            'profile_picture' => $row['profile_picture'],
        ];
    }

    http_response_code(200);
    echo json_encode([
        'message' => 'Search complete.',
        'users' => $users,
        'status' => 'success',
    ]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Database error: ' . $e->getMessage(), 'status' => 'error']);
}
