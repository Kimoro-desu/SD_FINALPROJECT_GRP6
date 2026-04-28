<?php
namespace Utils;

function ensureRatingsSchema(\PDO $db): void
{
    // users.reward_points (simple ecommerce-style reputation points)
    $userCols = $db->query("SHOW COLUMNS FROM users")->fetchAll(\PDO::FETCH_COLUMN, 0);
    if (!in_array('reward_points', $userCols, true)) {
        $db->exec("ALTER TABLE users ADD COLUMN reward_points INT NOT NULL DEFAULT 0");
    }

    // transactions.rating_locked
    // 0 = no pending ratings for this transaction
    // 1 = transaction is waiting for post-transaction ratings
    $txCols = $db->query("SHOW COLUMNS FROM transactions")->fetchAll(\PDO::FETCH_COLUMN, 0);
    if (!in_array('rating_locked', $txCols, true)) {
        $db->exec("ALTER TABLE transactions ADD COLUMN rating_locked TINYINT(1) NOT NULL DEFAULT 0 AFTER penalty_amount");
    }

    // Ratings table for mutual reviews after return
    $db->exec("
        CREATE TABLE IF NOT EXISTS transaction_ratings (
            rating_id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_id INT NOT NULL,
            rater_id INT NOT NULL,
            ratee_id INT NOT NULL,
            rating TINYINT UNSIGNED NOT NULL,
            points_awarded INT NOT NULL DEFAULT 0,
            review_text VARCHAR(500) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_tx_rater (transaction_id, rater_id),
            KEY idx_tx (transaction_id),
            KEY idx_ratee (ratee_id),
            KEY idx_rater (rater_id),
            CONSTRAINT chk_rating_range CHECK (rating >= 1 AND rating <= 5)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Keep lock state consistent for existing rows:
    // - Returned transactions are locked until both sides rate.
    // - Others remain unlocked.
    $db->exec("
        UPDATE transactions t
        LEFT JOIN (
            SELECT transaction_id, COUNT(*) AS rating_count
            FROM transaction_ratings
            GROUP BY transaction_id
        ) r ON r.transaction_id = t.transaction_id
        SET t.rating_locked = CASE
            WHEN t.request_status = 'Returned' AND COALESCE(r.rating_count, 0) < 2 THEN 1
            ELSE 0
        END
    ");
}
?>
