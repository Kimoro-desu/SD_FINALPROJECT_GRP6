<?php
namespace Utils;

function ensurePenaltySchema(\PDO $db): void
{
    // assets.daily_penalty
    $assetCols = $db->query("SHOW COLUMNS FROM assets")->fetchAll(\PDO::FETCH_COLUMN, 0);
    if (!in_array('daily_penalty', $assetCols, true)) {
        $db->exec("ALTER TABLE assets ADD COLUMN daily_penalty DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER description");
    }

    // transactions.borrowed_at + transactions.penalty_amount
    $txCols = $db->query("SHOW COLUMNS FROM transactions")->fetchAll(\PDO::FETCH_COLUMN, 0);
    if (!in_array('borrowed_at', $txCols, true)) {
        $db->exec("ALTER TABLE transactions ADD COLUMN borrowed_at DATETIME NULL DEFAULT NULL AFTER time_created");
    }
    if (!in_array('penalty_amount', $txCols, true)) {
        $db->exec("ALTER TABLE transactions ADD COLUMN penalty_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER return_date");
    }
}
?>

