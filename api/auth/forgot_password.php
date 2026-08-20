<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/system_logger.php';
require_once '../../utils/user_account_schema.php';

use Config\Database;
use Utils\SystemLogger;

use function Utils\ensureUserAccountSchema;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed.", "status" => "error"]);
    exit();
}

$database = new Database();
$db = $database->getConnection();
ensureUserAccountSchema($db);

$data = json_decode(file_get_contents("php://input"));
$ip = $_SERVER['REMOTE_ADDR'] ?? null;

$email = trim((string)($data->email ?? ''));
$schoolId = trim((string)($data->school_id ?? ''));
$newPassword = (string)($data->new_password ?? '');

if ($email === '' || $schoolId === '' || $newPassword === '') {
    http_response_code(400);
    echo json_encode(["message" => "Email, school ID, and new password are required.", "status" => "error"]);
    exit();
}

if (strlen($newPassword) < 8) {
    http_response_code(400);
    echo json_encode(["message" => "New password must be at least 8 characters.", "status" => "error"]);
    exit();
}

try {
    $query = "SELECT User_ID, plm_email FROM users WHERE plm_email = :email AND school_id_number = :school_id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':school_id', $schoolId);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        SystemLogger::log($db, 'security', 'Forgot-password failed: account not matched.', $email, $ip, 'Failed');
        echo json_encode(["message" => "No account matches the provided email and ID number.", "status" => "error"]);
        exit();
    }

    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
    $upd = $db->prepare("UPDATE users SET password_hash = :pwd WHERE User_ID = :uid");
    $upd->bindValue(':pwd', $newHash);
    $upd->bindValue(':uid', (int)$user['User_ID'], PDO::PARAM_INT);
    $upd->execute();

    SystemLogger::log(
        $db,
        'auth',
        'Password reset via forgot-password flow (User_ID: ' . (int)$user['User_ID'] . ').',
        (string)$user['plm_email'],
        $ip,
        'Success'
    );

    http_response_code(200);
    echo json_encode(["message" => "Password updated successfully.", "status" => "success"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>
