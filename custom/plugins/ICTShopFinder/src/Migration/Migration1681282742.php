<?php declare(strict_types=1);

namespace ICTShopFinder\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1681282742 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1681282742;
    }

    public function update(Connection $connection): void
    {
        // implement update
        $sqlShopFinder ="CREATE TABLE IF NOT EXISTS `ict_shop_finder` (
            `id` BINARY(16) NOT NULL,
            `product_version_id` BINARY(16) NULL,
            `not_translated_field` VARCHAR(255) NULL,
            `active` TINYINT(1) NULL DEFAULT '0',
            `description` LONGTEXT NULL,
            `post_code` VARCHAR(255) NOT NULL,
            `url` VARCHAR(255) NULL,
            `telephone` VARCHAR(255) NULL,
            `open_times` LONGTEXT NULL,
            `country_id` BINARY(16) NULL,
            `created_at` DATETIME(3) NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`),
            KEY `fk.ict_shop_finder.country_id` (`country_id`),
            CONSTRAINT `fk.ict_shop_finder.country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $connection->executeStatement($sqlShopFinder);

        $sqlTranslation = "CREATE TABLE `ict_shop_finder_translation` (
                        `name` VARCHAR(255) NOT NULL,
                        `street` VARCHAR(255) NOT NULL,
                        `city` VARCHAR(255) NULL,
                        `created_at` DATETIME(3) NOT NULL,
                        `updated_at` DATETIME(3) NULL,
                        `ict_shop_finder_id` BINARY(16) NOT NULL,
                        `language_id` BINARY(16) NOT NULL,
                        PRIMARY KEY (`ict_shop_finder_id`,`language_id`),
                        KEY `fk.ict_shop_finder_translation.ict_shop_finder_id` (`ict_shop_finder_id`),
                        KEY `fk.ict_shop_finder_translation.language_id` (`language_id`),
                        CONSTRAINT `fk.ict_shop_finder_translation.ict_shop_finder_id` FOREIGN KEY (`ict_shop_finder_id`) REFERENCES `ict_shop_finder` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                        CONSTRAINT `fk.ict_shop_finder_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $connection->executeStatement($sqlTranslation);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
