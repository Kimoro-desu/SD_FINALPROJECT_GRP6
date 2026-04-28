<?php
namespace Utils;

function ensureUserNotificationsTable(\PDO $db): void
{
    $db->exec("
        CREATE TABLE IF NOT EXISTS user_notifications (
            notification_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            severity VARCHAR(32) NOT NULL DEFAULT 'info',
            icon_class VARCHAR(64) NOT NULL DEFAULT 'ph-info',
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            INDEX idx_user_notifications_user_time (user_id, created_at),
            CONSTRAINT fk_user_notifications_user FOREIGN KEY (user_id) REFERENCES users(User_ID) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
}

function pushUserNotification(\PDO $db, int $userId, string $title, string $message, string $severity = 'info', string $iconClass = 'ph-info'): void
{
    ensureUserNotificationsTable($db);
    $stmt = $db->prepare("
        INSERT INTO user_notifications (user_id, title, message, severity, icon_class)
        VALUES (:user_id, :title, :message, :severity, :icon_class)
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':title' => $title,
        ':message' => $message,
        ':severity' => $severity,
        ':icon_class' => $iconClass
    ]);
}

function ensureNotificationReadColumn(\PDO $db): void
{
    $cols = $db->query("SHOW COLUMNS FROM user_notifications")->fetchAll(\PDO::FETCH_COLUMN, 0);
    if (!in_array('is_read', $cols, true)) {
        $db->exec("ALTER TABLE user_notifications ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER created_at");
    }
}
?>

