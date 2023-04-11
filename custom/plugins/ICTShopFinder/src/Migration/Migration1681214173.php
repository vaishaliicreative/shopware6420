<?php declare(strict_types=1);

namespace ICTShopFinder\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1681214173 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1681214173;
    }

    public function update(Connection $connection): void
    {
        $sql ="CREATE TABLE IF NOT EXISTS `ict_shop_finder` (
                `id` BINARY(16) NOT NULL,
                `active` TINYINT(1) NULL DEFAULT '0',
                `name` VARCHAR(255) NOT NULL,
                `description` LONGTEXT NULL,
                `street` VARCHAR(255) NOT NULL,
                `post_code` VARCHAR(255) NOT NULL,
                `city` VARCHAR(255) NOT NULL,
                `url` VARCHAR(255) NULL,
                `telephone` VARCHAR(255) NULL,
                `open_times` LONGTEXT NULL,
                `country_id` BINARY(16) NULL,
                `created_at` DATETIME(3) NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                KEY `fk.ict_shop_finder.country_id` (`country_id`),
                CONSTRAINT `fk.ict_shop_finder.country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
