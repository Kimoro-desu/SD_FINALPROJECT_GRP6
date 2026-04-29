<?php
namespace Utils;

function ensurePenaltySchema(\PDO $db): void
{
    // assets.daily_penalty  (now an integer — no decimals)
    $assetCols = $db->query("SHOW COLUMNS FROM assets")->fetchAll(\PDO::FETCH_COLUMN, 0);
    if (!in_array('daily_penalty', $assetCols, true)) {
        $db->exec("ALTER TABLE assets ADD COLUMN daily_penalty INT NOT NULL DEFAULT 0 AFTER description");
    }

    // assets.penalty_type  ('per_day' or 'per_hour')
    if (!in_array('penalty_type', $assetCols, true)) {
        $db->exec("ALTER TABLE assets ADD COLUMN penalty_type VARCHAR(16) NOT NULL DEFAULT 'per_day' AFTER daily_penalty");
    }

    // transactions.borrowed_at + transactions.penalty_amount
    $txCols = $db->query("SHOW COLUMNS FROM transactions")->fetchAll(\PDO::FETCH_COLUMN, 0);
    if (!in_array('borrowed_at', $txCols, true)) {
        $db->exec("ALTER TABLE transactions ADD COLUMN borrowed_at DATETIME NULL DEFAULT NULL AFTER time_created");
    }
    if (!in_array('penalty_amount', $txCols, true)) {
        $db->exec("ALTER TABLE transactions ADD COLUMN penalty_amount INT NOT NULL DEFAULT 0 AFTER return_date");
    }
}
?>
