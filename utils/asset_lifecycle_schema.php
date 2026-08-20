<?php
namespace Utils;

function ensureAssetLifecycleSchema(\PDO $db): void
{
    // Detect existing columns
    $colStmt = $db->query("SHOW COLUMNS FROM assets");
    $columns = [];
    while ($row = $colStmt->fetch(\PDO::FETCH_ASSOC)) {
        $columns[strtolower((string)$row['Field'])] = true;
    }

    // Add lifecycle status if missing
    if (!isset($columns['status'])) {
        $db->exec("ALTER TABLE assets ADD COLUMN status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
    }

    // Ensure availability column exists
    if (!isset($columns['availability'])) {
        $db->exec("ALTER TABLE assets ADD COLUMN availability VARCHAR(50) DEFAULT 'unavailable'");
    }

    // Scheduled availability window (optional)
    if (!isset($columns['available_from'])) {
        $db->exec("ALTER TABLE assets ADD COLUMN available_from DATETIME NULL DEFAULT NULL");
    }
    if (!isset($columns['available_until'])) {
        $db->exec("ALTER TABLE assets ADD COLUMN available_until DATETIME NULL DEFAULT NULL");
    }

    // Optional workflow metadata fields requested by product flow
    if (!isset($columns['meetup_location'])) {
        $db->exec("ALTER TABLE assets ADD COLUMN meetup_location VARCHAR(255) NULL DEFAULT NULL");
    }
    if (!isset($columns['proposed_penalty_amount'])) {
        $db->exec("ALTER TABLE assets ADD COLUMN proposed_penalty_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00");
    }

    // Normalize legacy availability values to lifecycle values
    $db->exec("
        UPDATE assets
        SET availability = CASE
            WHEN LOWER(COALESCE(availability, '')) = 'available' THEN 'available'
            ELSE 'unavailable'
        END
    ");
}
?>

