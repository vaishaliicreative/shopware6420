<?php declare(strict_types=1);

namespace ICTECHBackendLoginByOTP\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1683549712 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1683549712;
    }

    public function update(Connection $connection): void
    {
        // implement update
        $connection->executeStatement("
            CREATE TABLE IF NOT EXISTS `backend_login_by_otp` (
                `id` BINARY(16) NOT NULL,
                `user_id` BINARY(16) NOT NULL,
                `otp` VARCHAR(255) NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                KEY `fk.backend_login_by_otp.user_id` (`user_id`),
                CONSTRAINT `fk.backend_login_by_otp.user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
