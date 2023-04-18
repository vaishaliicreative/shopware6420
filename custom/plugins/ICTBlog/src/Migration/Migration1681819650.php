<?php declare(strict_types=1);

namespace ICTBlog\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1681819650 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1681819650;
    }

    public function update(Connection $connection): void
    {
        // implement update
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
