<?php declare(strict_types=1);

namespace ICTBlog\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1681987767 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1681987767;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("
            CREATE TABLE IF NOT EXISTS `ict_blog_category` (
                `id` BINARY(16) NOT NULL,
                `not_translated_field` VARCHAR(255) NULL,
                `active` TINYINT(1) NULL DEFAULT '0',
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $connection->executeStatement("
            CREATE TABLE IF NOT EXISTS `ict_blog_category_translation` (
                `name` VARCHAR(255) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                `ict_blog_category_id` BINARY(16) NOT NULL,
                `language_id` BINARY(16) NOT NULL,
                PRIMARY KEY (`ict_blog_category_id`,`language_id`),
                KEY `fk.ict_blog_category_translation.ict_blog_category_id` (`ict_blog_category_id`),
                KEY `fk.ict_blog_category_translation.language_id` (`language_id`),
                CONSTRAINT `fk.ict_blog_category_translation.ict_blog_category_id` FOREIGN KEY (`ict_blog_category_id`) REFERENCES `ict_blog_category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.ict_blog_category_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $connection->executeStatement("
            CREATE TABLE IF NOT EXISTS `ict_blog` (
                `id` BINARY(16) NOT NULL,
                `release_date` DATE NULL,
                `active` TINYINT(1) NULL DEFAULT '0',
                `author` VARCHAR(255) NOT NULL,
                `not_translated_field` VARCHAR(255) NULL,
                `category_ids` JSON NULL,
                `product_ids` JSON NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `json.ict_blog.category_ids` CHECK (JSON_VALID(`category_ids`)),
                CONSTRAINT `json.ict_blog.product_ids` CHECK (JSON_VALID(`product_ids`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $connection->executeStatement("
            CREATE TABLE IF NOT EXISTS `ict_blog_translation` (
                `name` VARCHAR(255) NOT NULL,
                `description` LONGTEXT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                `ict_blog_id` BINARY(16) NOT NULL,
                `language_id` BINARY(16) NOT NULL,
                PRIMARY KEY (`ict_blog_id`,`language_id`),
                KEY `fk.ict_blog_translation.ict_blog_id` (`ict_blog_id`),
                KEY `fk.ict_blog_translation.language_id` (`language_id`),
                CONSTRAINT `fk.ict_blog_translation.ict_blog_id` FOREIGN KEY (`ict_blog_id`) REFERENCES `ict_blog` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.ict_blog_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $connection->executeStatement("
            CREATE TABLE IF NOT EXISTS `ict_blog_product_map` (
                `blog_id` BINARY(16) NOT NULL,
                `product_id` BINARY(16) NOT NULL,
                `product_version_id` BINARY(16) NULL,
                PRIMARY KEY (`blog_id`,`product_id`),
                KEY `fk.ict_blog_product_map.blog_id` (`blog_id`),
                KEY `fk.ict_blog_product_map.product_id` (`product_id`,`product_version_id`),
                CONSTRAINT `fk.ict_blog_product_map.blog_id` FOREIGN KEY (`blog_id`) REFERENCES `ict_blog` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.ict_blog_product_map.product_id` FOREIGN KEY (`product_id`,`product_version_id`) REFERENCES `product` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $connection->executeStatement("
            CREATE TABLE IF NOT EXISTS `ict_blog_category_map` (
                `blog_category_id` BINARY(16) NOT NULL,
                `blog_id` BINARY(16) NOT NULL,
                `category_version_id` BINARY(16) NULL,
                PRIMARY KEY (`blog_category_id`,`blog_id`),
                KEY `fk.ict_blog_category_map.blog_category_id` (`blog_category_id`),
                KEY `fk.ict_blog_category_map.blog_id` (`blog_id`),
                CONSTRAINT `fk.ict_blog_category_map.blog_category_id` FOREIGN KEY (`blog_category_id`) REFERENCES `ict_blog_category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.ict_blog_category_map.blog_id` FOREIGN KEY (`blog_id`) REFERENCES `ict_blog` (`id`) ON DELETE CASCADE     ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
