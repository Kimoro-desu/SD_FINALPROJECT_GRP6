<?php
namespace Utils;

class SystemLogger
{
    private static function ensureTables(\PDO $db)
    {
        // Minimal audit table for Admin Console "System Logs"
        $db->exec("
            CREATE TABLE IF NOT EXISTS system_logs (
                log_id INT AUTO_INCREMENT PRIMARY KEY,
                event_type VARCHAR(32) NOT NULL,
                description TEXT NOT NULL,
                actor VARCHAR(255) DEFAULT NULL,
                ip_address VARCHAR(64) DEFAULT NULL,
                status VARCHAR(32) NOT NULL DEFAULT 'Success',
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");

        // Registration approvals queue
        $db->exec("
            CREATE TABLE IF NOT EXISTS registration_requests (
                request_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                status VARCHAR(32) NOT NULL DEFAULT 'Pending',
                requested_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                processed_at DATETIME DEFAULT NULL,
                processed_by INT DEFAULT NULL,
                UNIQUE KEY uniq_user_request (user_id),
                CONSTRAINT fk_regreq_user FOREIGN KEY (user_id) REFERENCES users(User_ID) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
    }

    public static function log(\PDO $db, string $eventType, string $description, ?string $actor, ?string $ip, string $status = 'Success')
    {
        self::ensureTables($db);
        $stmt = $db->prepare("
            INSERT INTO system_logs (event_type, description, actor, ip_address, status)
            VALUES (:event_type, :description, :actor, :ip_address, :status)
        ");
        $stmt->execute([
            ':event_type' => $eventType,
            ':description' => $description,
            ':actor' => $actor,
            ':ip_address' => $ip,
            ':status' => $status
        ]);
    }

    public static function ensureRegistrationRequest(\PDO $db, int $userId)
    {
        self::ensureTables($db);
        // Create a pending request if missing
        $stmt = $db->prepare("INSERT IGNORE INTO registration_requests (user_id, status) VALUES (:uid, 'Pending')");
        $stmt->execute([':uid' => $userId]);
    }
}

