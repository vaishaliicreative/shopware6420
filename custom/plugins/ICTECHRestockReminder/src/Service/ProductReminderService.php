<?php

declare(strict_types=1);

namespace ICTECHRestockReminder\Service;

use Shopware\Core\System\SalesChannel\Context\CachedSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextPersister;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Class ProductReminderService
 *
 * @package ICTECHRestockReminder\Service
 */
class ProductReminderService
{
    protected ConfigService $configService;

    protected SalesChannelContextPersister $contextPersister;

    private CachedSalesChannelContextFactory $salesChannelContextFactory;

    public function __construct(
        ConfigService $configService,
        SalesChannelContextPersister $contextPersister,
        CachedSalesChannelContextFactory $salesChannelContextFactory
    ) {
        $this->configService = $configService;
        $this->contextPersister = $contextPersister;
        $this->salesChannelContextFactory = $salesChannelContextFactory;
    }

    public function isActive(SalesChannelEntity $salesChannelEntity): bool
    {
        return $this->configService->getIsActive($salesChannelEntity);
    }

    public function getInterval(): int
    {
        return $this->configService->getInterval();
    }

    public function setName(
        ?SalesChannelEntity $salesChannelEntity = null,
        string $name
    ): void {
        $this->configService->setName($salesChannelEntity, $name);
    }

    public function setStatus(
        ?SalesChannelEntity $salesChannelEntity = null,
        string $status
    ): void {
        $this->configService->setStatus($salesChannelEntity, $status);
    }
}
