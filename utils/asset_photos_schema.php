<?php
namespace Utils;

/**
 * Ensures the `asset_image` column exists on the `assets` table
 * and the `return_photos` table exists for borrower return evidence.
 * Safe to call multiple times – uses IF NOT EXISTS / column checks.
 */
function ensureAssetPhotosSchema(\PDO $db): void
{
    // 1. Add `asset_image` column to `assets` if it doesn't exist
    $cols = $db->query("SHOW COLUMNS FROM `assets` LIKE 'asset_image'")->fetchAll();
    if (count($cols) === 0) {
        $db->exec("ALTER TABLE `assets` ADD COLUMN `asset_image` VARCHAR(512) DEFAULT NULL AFTER `time_created`");
    }

    // 2. Create `return_photos` table if not exists
    $db->exec("
        CREATE TABLE IF NOT EXISTS `return_photos` (
            `photo_id` INT(11) NOT NULL AUTO_INCREMENT,
            `transaction_id` INT(11) NOT NULL,
            `photo_path` VARCHAR(512) NOT NULL,
            `uploaded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`photo_id`),
            KEY `fk_return_photos_txn` (`transaction_id`),
            CONSTRAINT `fk_return_photos_txn` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
}
