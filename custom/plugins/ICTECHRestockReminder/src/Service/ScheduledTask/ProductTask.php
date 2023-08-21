<?php declare(strict_types=1);

namespace ICTECHRestockReminder\Service\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class ProductTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'ICTECH.product_task';
    }

    public static function getDefaultInterval(): int
    {
        return 300;
    }
}
