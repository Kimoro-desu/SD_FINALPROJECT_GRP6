<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../utils/jwt_helper.php';
require_once '../../utils/penalty_schema.php';
require_once '../../utils/ratings_schema.php';
require_once '../../utils/asset_photos_schema.php';

use Config\Database;
use Utils\JwtHelper;
use function Utils\ensurePenaltySchema;
use function Utils\ensureRatingsSchema;
use function Utils\ensureAssetPhotosSchema;

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
ensurePenaltySchema($db);
ensureRatingsSchema($db);
try { ensureAssetPhotosSchema($db); } catch (\Exception $e) { error_log('ensureAssetPhotosSchema: ' . $e->getMessage()); }

try {
    // Admin/Staff can see all transactions.
    // Other users can see:
    // 1) transactions they requested as borrower
    // 2) transactions for assets they own (lender-side pending approvals)
    $role = strtolower(trim((string)$decodedData['role']));
    $canViewAll = in_array($role, [
        strtolower(Database::ROLE_ADMIN),
        strtolower(Database::ROLE_STAFF)
    ], true);
    if ($canViewAll) {
        // Fetch all transactions
        $query = "SELECT t.transaction_id, t.asset_id, t.borrower_id, t.request_status, t.time_created as request_date, t.borrowed_at, t.due_date, t.return_date, t.penalty_amount, t.rating_locked,
                         a.asset_name, a.daily_penalty, a.meetup_location, a.proposed_penalty_amount, a.Lender_ID, a.asset_image,
                         u.first_name, u.last_name, u.school_id_number, u.plm_email,
                         lu.first_name AS lender_first_name, lu.last_name AS lender_last_name, lu.plm_email AS lender_email
                  FROM transactions t
                  JOIN assets a ON t.asset_id = a.Asset_ID
                  JOIN users u ON t.borrower_id = u.User_ID
                  LEFT JOIN users lu ON a.Lender_ID = lu.User_ID
                  ORDER BY t.time_created DESC";
        $stmt = $db->prepare($query);
    } else {
        // Fetch borrower + lender-owned asset transactions for this user.
        $query = "SELECT t.transaction_id, t.asset_id, t.borrower_id, t.request_status, t.time_created as request_date, t.borrowed_at, t.due_date, t.return_date, t.penalty_amount, t.rating_locked,
                         a.asset_name, a.daily_penalty, a.meetup_location, a.proposed_penalty_amount, a.Lender_ID, a.asset_image,
                         u.first_name, u.last_name, u.school_id_number, u.plm_email,
                         lu.first_name AS lender_first_name, lu.last_name AS lender_last_name, lu.plm_email AS lender_email
                  FROM transactions t
                  JOIN assets a ON t.asset_id = a.Asset_ID
                  JOIN users u ON t.borrower_id = u.User_ID
                  LEFT JOIN users lu ON a.Lender_ID = lu.User_ID
                  WHERE (t.borrower_id = :uid OR a.Lender_ID = :uid)
                  ORDER BY t.time_created DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':uid', $decodedData['id']);
    }

    $stmt->execute();
    $num = $stmt->rowCount();

    $history_arr = array();
    
    $txIds = [];
    if ($num > 0) {
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $txIds[] = (int)$row['transaction_id'];
            array_push($history_arr, [
                "transaction_id" => $row['transaction_id'],
                "asset" => [
                    "id" => $row['asset_id'],
                    "name" => $row['asset_name'],
                    "daily_penalty" => (float)($row['daily_penalty'] ?? 0),
                    "proposed_penalty_amount" => (float)($row['proposed_penalty_amount'] ?? 0),
                    "meetup_location" => $row['meetup_location'],
                    "asset_image" => $row['asset_image'] ?? null
                ],
                "borrower" => [
                    "id" => $row['borrower_id'],
                    "school_id" => $row['school_id_number'],
                    "name" => trim($row['first_name'] . ' ' . $row['last_name']),
                    "email" => $row['plm_email']
                ],
                "lender" => [
                    "id" => (int)$row['Lender_ID'],
                    "name" => trim(($row['lender_first_name'] ?? '') . ' ' . ($row['lender_last_name'] ?? '')),
                    "email" => $row['lender_email']
                ],
                "status" => $row['request_status'],
                "penalty_amount" => (float)($row['penalty_amount'] ?? 0),
                "rating_locked" => (int)($row['rating_locked'] ?? 0),
                "is_overdue" => (!empty($row['due_date']) && empty($row['return_date']) && strtotime($row['due_date']) < time()),
                "dates" => [
                    "requested" => $row['request_date'],
                    "borrowed" => $row['borrowed_at'],
                    "due" => $row['due_date'],
                    "returned" => $row['return_date']
                ]
            ]);
        }
    }

    // Attach current-user rating context for UI actions.
    if (!empty($txIds)) {
        $idPlaceholders = implode(',', array_fill(0, count($txIds), '?'));
        $ratingSql = "SELECT transaction_id, rater_id, ratee_id, rating, review_text
                      FROM transaction_ratings
                      WHERE transaction_id IN ($idPlaceholders)";
        $ratingStmt = $db->prepare($ratingSql);
        foreach ($txIds as $i => $txId) {
            $ratingStmt->bindValue($i + 1, $txId, \PDO::PARAM_INT);
        }
        $ratingStmt->execute();
        $ratingsByTx = [];
        while ($r = $ratingStmt->fetch(\PDO::FETCH_ASSOC)) {
            $tid = (int)$r['transaction_id'];
            if (!isset($ratingsByTx[$tid])) {
                $ratingsByTx[$tid] = [];
            }
            $ratingsByTx[$tid][] = $r;
        }

        $currentUserId = (int)$decodedData['id'];
        foreach ($history_arr as &$txItem) {
            $tid = (int)$txItem['transaction_id'];
            $borrowerId = (int)($txItem['borrower']['id'] ?? 0);
            $lenderId = (int)($txItem['lender']['id'] ?? 0);
            $isBorrower = $currentUserId === $borrowerId;
            $isLender = $currentUserId === $lenderId;
            $counterparty = $isBorrower ? $txItem['lender'] : $txItem['borrower'];

            $myRating = null;
            $counterpartyRating = null;
            $list = $ratingsByTx[$tid] ?? [];
            foreach ($list as $r) {
                if ((int)$r['rater_id'] === $currentUserId) {
                    $myRating = [
                        "rating" => (int)$r['rating'],
                        "review_text" => $r['review_text']
                    ];
                } elseif ((int)$r['rater_id'] === (int)($counterparty['id'] ?? 0)) {
                    $counterpartyRating = [
                        "rating" => (int)$r['rating'],
                        "review_text" => $r['review_text']
                    ];
                }
            }

            $canRate = (
                $txItem['status'] === Database::STATUS_RETURNED &&
                (int)$txItem['rating_locked'] === 1 &&
                ($isBorrower || $isLender) &&
                $myRating === null &&
                (int)($counterparty['id'] ?? 0) > 0
            );

            $txItem['is_current_user_borrower'] = $isBorrower;
            $txItem['is_current_user_lender'] = $isLender;
            // Determine the user's role in this transaction for frontend tab filtering
            $txItem['role'] = $isBorrower ? 'borrower' : 'lender';
            $txItem['counterparty'] = $counterparty;
            $txItem['my_rating'] = $myRating;
            $txItem['counterparty_rating'] = $counterpartyRating;
            $txItem['can_rate'] = $canRate;
        }
        unset($txItem);
    }

    // Attach return photos to each transaction
    if (!empty($txIds)) {
        $idPlaceholders2 = implode(',', array_fill(0, count($txIds), '?'));
        $photoSql = "SELECT transaction_id, photo_path, uploaded_at
                     FROM return_photos
                     WHERE transaction_id IN ($idPlaceholders2)
                     ORDER BY uploaded_at ASC";
        $photoStmt = $db->prepare($photoSql);
        foreach ($txIds as $i => $txId) {
            $photoStmt->bindValue($i + 1, $txId, \PDO::PARAM_INT);
        }
        $photoStmt->execute();
        $photosByTx = [];
        while ($p = $photoStmt->fetch(\PDO::FETCH_ASSOC)) {
            $tid = (int)$p['transaction_id'];
            if (!isset($photosByTx[$tid])) {
                $photosByTx[$tid] = [];
            }
            $photosByTx[$tid][] = [
                'photo_path' => $p['photo_path'],
                'uploaded_at' => $p['uploaded_at']
            ];
        }
        foreach ($history_arr as &$txItem) {
            $tid = (int)$txItem['transaction_id'];
            $txItem['return_photos'] = $photosByTx[$tid] ?? [];
        }
        unset($txItem);
    }
    
    http_response_code(200);
    echo json_encode(["message" => "Transaction history fetched successfully.", "history" => $history_arr, "status" => "success"]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage(), "status" => "error"]);
}
?>
