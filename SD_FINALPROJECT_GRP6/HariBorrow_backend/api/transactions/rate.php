<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
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
ensureRatingsSchema($db);

$data = json_decode(file_get_contents("php://input"));
$transactionId = isset($data->transaction_id) ? (int)$data->transaction_id : 0;
$rating = isset($data->rating) ? (int)$data->rating : 0;
$reviewText = isset($data->review_text) ? trim((string)$data->review_text) : '';

if ($transactionId <= 0 || $rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(["message" => "transaction_id and rating (1-5) are required.", "status" => "error"]);
    exit();
}

$reviewText = mb_substr($reviewText, 0, 500);
$raterId = (int)$decodedData['id'];

try {
    $db->beginTransaction();

    $txStmt = $db->prepare("
        SELECT transaction_id, asset_id, borrower_id, request_status, rating_locked
        FROM transactions
        WHERE transaction_id = :tid
        FOR UPDATE
    ");
    $txStmt->bindValue(':tid', $transactionId, \PDO::PARAM_INT);
    $txStmt->execute();
    $tx = $txStmt->fetch(\PDO::FETCH_ASSOC);

    if (!$tx) {
        $db->rollBack();
        http_response_code(404);
        echo json_encode(["message" => "Transaction not found.", "status" => "error"]);
        exit();
    }

    if ($tx['request_status'] !== Database::STATUS_RETURNED) {
        $db->rollBack();
        http_response_code(400);
        echo json_encode(["message" => "Rating is only allowed after return completion.", "status" => "error"]);
        exit();
    }

    if ((int)$tx['rating_locked'] !== 1) {
        $db->rollBack();
        http_response_code(400);
        echo json_encode(["message" => "This transaction is no longer open for ratings.", "status" => "error"]);
        exit();
    }

    $assetStmt = $db->prepare("SELECT Lender_ID FROM assets WHERE Asset_ID = :aid LIMIT 1");
    $assetStmt->bindValue(':aid', (int)$tx['asset_id'], \PDO::PARAM_INT);
    $assetStmt->execute();
    $assetRow = $assetStmt->fetch(\PDO::FETCH_ASSOC);
    $lenderId = (int)($assetRow['Lender_ID'] ?? 0);
    $borrowerId = (int)$tx['borrower_id'];

    if ($raterId !== $borrowerId && $raterId !== $lenderId) {
        $db->rollBack();
        http_response_code(403);
        echo json_encode(["message" => "Only transaction participants can rate each other.", "status" => "error"]);
        exit();
    }

    $rateeId = ($raterId === $borrowerId) ? $lenderId : $borrowerId;
    if ($rateeId <= 0 || $rateeId === $raterId) {
        $db->rollBack();
        http_response_code(400);
        echo json_encode(["message" => "Invalid counterpart user for this rating.", "status" => "error"]);
        exit();
    }

    $existingStmt = $db->prepare("
        SELECT rating_id
        FROM transaction_ratings
        WHERE transaction_id = :tid AND rater_id = :rid
        LIMIT 1
    ");
    $existingStmt->bindValue(':tid', $transactionId, \PDO::PARAM_INT);
    $existingStmt->bindValue(':rid', $raterId, \PDO::PARAM_INT);
    $existingStmt->execute();
    if ($existingStmt->fetch(\PDO::FETCH_ASSOC)) {
        $db->rollBack();
        http_response_code(409);
        echo json_encode(["message" => "You already submitted a rating for this transaction.", "status" => "error"]);
        exit();
    }

    $points = $rating * 10;
    $insertStmt = $db->prepare("
        INSERT INTO transaction_ratings (transaction_id, rater_id, ratee_id, rating, points_awarded, review_text)
        VALUES (:tid, :rater_id, :ratee_id, :rating, :points, :review_text)
    ");
    $insertStmt->bindValue(':tid', $transactionId, \PDO::PARAM_INT);
    $insertStmt->bindValue(':rater_id', $raterId, \PDO::PARAM_INT);
    $insertStmt->bindValue(':ratee_id', $rateeId, \PDO::PARAM_INT);
    $insertStmt->bindValue(':rating', $rating, \PDO::PARAM_INT);
    $insertStmt->bindValue(':points', $points, \PDO::PARAM_INT);
    $insertStmt->bindValue(':review_text', $reviewText !== '' ? $reviewText : null, \PDO::PARAM_STR);
    $insertStmt->execute();

    $pointsStmt = $db->prepare("UPDATE users SET reward_points = COALESCE(reward_points, 0) + :pts WHERE User_ID = :uid");
    $pointsStmt->bindValue(':pts', $points, \PDO::PARAM_INT);
    $pointsStmt->bindValue(':uid', $rateeId, \PDO::PARAM_INT);
    $pointsStmt->execute();

    $countStmt = $db->prepare("
        SELECT COUNT(*) AS total
        FROM transaction_ratings
        WHERE transaction_id = :tid
          AND rater_id IN (:borrower_id, :lender_id)
    ");
    $countStmt->bindValue(':tid', $transactionId, \PDO::PARAM_INT);
    $countStmt->bindValue(':borrower_id', $borrowerId, \PDO::PARAM_INT);
    $countStmt->bindValue(':lender_id', $lenderId, \PDO::PARAM_INT);
    $countStmt->execute();
    $totalRatings = (int)($countStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);

    $stillLocked = 1;
    if ($totalRatings >= 2) {
        $unlockStmt = $db->prepare("UPDATE transactions SET rating_locked = 0 WHERE transaction_id = :tid");
        $unlockStmt->bindValue(':tid', $transactionId, \PDO::PARAM_INT);
        $unlockStmt->execute();
        $stillLocked = 0;
    }

    $db->commit();
    http_response_code(201);
    echo json_encode([
        "message" => "Rating submitted successfully.",
        "transaction_id" => $transactionId,
        "points_awarded" => $points,
        "rating_locked" => $stillLocked,
        "status" => "success"
    ]);
} catch (\PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>
