<?php declare(strict_types=1);

namespace ICTTask\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1681467865 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1681467865;
    }

    public function update(Connection $connection): void
    {
        // implement update
        $connection->executeStatement("CREATE TABLE IF NOT EXISTS `ict_task` (
                `id` BINARY(16) NOT NULL,
                `product_version_id` BINARY(16) NULL,
                `active` TINYINT(1) NULL DEFAULT '0',
                `country_id` BINARY(16) NULL,
                `country_state_id` BINARY(16) NULL,
                `product_id` BINARY(16) NULL,
                `media_id` BINARY(16) NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                KEY `fk.ict_task.country_id` (`country_id`),
                KEY `fk.ict_task.country_state_id` (`country_state_id`),
                KEY `fk.ict_task.product_id` (`product_id`,`product_version_id`),
                CONSTRAINT `fk.ict_task.country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.ict_task.country_state_id` FOREIGN KEY (`country_state_id`) REFERENCES `country_state` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.ict_task.product_id` FOREIGN KEY (`product_id`,`product_version_id`) REFERENCES `product` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $connection->executeStatement("CREATE TABLE IF NOT EXISTS `ict_task_translation` (
                    `name` VARCHAR(255) NOT NULL,
                    `city` VARCHAR(255) NOT NULL,
                    `created_at` DATETIME(3) NOT NULL,
                    `updated_at` DATETIME(3) NULL,
                    `ict_task_id` BINARY(16) NOT NULL,
                    `language_id` BINARY(16) NOT NULL,
                    PRIMARY KEY (`ict_task_id`,`language_id`),
                    KEY `fk.ict_task_translation.ict_task_id` (`ict_task_id`),
                    KEY `fk.ict_task_translation.language_id` (`language_id`),
                    CONSTRAINT `fk.ict_task_translation.ict_task_id` FOREIGN KEY (`ict_task_id`) REFERENCES `ict_task` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    CONSTRAINT `fk.ict_task_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
