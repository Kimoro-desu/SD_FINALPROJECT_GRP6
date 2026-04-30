<?php
namespace Utils;

/**
 * Ensures columns used for admin account status (active / hold / suspend) exist on `users`.
 */
function ensureUserAccountSchema(\PDO $db): void
{
    $cols = $db->query("SHOW COLUMNS FROM users")->fetchAll(\PDO::FETCH_COLUMN, 0);
    if (!in_array('account_status', $cols, true)) {
        $db->exec("ALTER TABLE users ADD COLUMN account_status VARCHAR(32) NOT NULL DEFAULT 'active' AFTER background_picture");
    }
    if (!in_array('account_notes', $cols, true)) {
        $db->exec("ALTER TABLE users ADD COLUMN account_notes VARCHAR(500) NULL DEFAULT NULL AFTER account_status");
    }
    if (!in_array('id_verification_status', $cols, true)) {
        $db->exec("ALTER TABLE users ADD COLUMN id_verification_status VARCHAR(32) NOT NULL DEFAULT 'unverified' AFTER account_notes");
    }
    if (!in_array('id_photo_url', $cols, true)) {
        $db->exec("ALTER TABLE users ADD COLUMN id_photo_url VARCHAR(500) NULL DEFAULT NULL AFTER id_verification_status");
    }
}
