<?php

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
    /**
     * @var ConfigService
     */
    protected $configService;

    /**
     * @var SalesChannelContextPersister
     */
    protected $contextPersister;

    /**
     * @var CachedSalesChannelContextFactory
     */
    private CachedSalesChannelContextFactory $salesChannelContextFactory;


    /**
     * AbandonedCartService constructor.
     *
     * @param ConfigService $configService
     * @param SalesChannelContextPersister $contextPersister
     * @param CachedSalesChannelContextFactory $salesChannelContextFactory
     */
    public function __construct(
        ConfigService                    $configService,
        SalesChannelContextPersister     $contextPersister,
        CachedSalesChannelContextFactory $salesChannelContextFactory
    )
    {
        $this->configService = $configService;
        $this->contextPersister = $contextPersister;
        $this->salesChannelContextFactory = $salesChannelContextFactory;
    }

    /**
     * @param SalesChannelEntity $salesChannelEntity
     *
     * @return bool
     */
    public function isActive(SalesChannelEntity $salesChannelEntity): bool
    {
        return $this->configService->getIsActive($salesChannelEntity);
    }

    /**
     * @return int
     */
    public function getInterval(): int
    {
        return $this->configService->getInterval();
    }

    /**
     * @param SalesChannelEntity|null $salesChannelEntity
     * @param $name
     */
    public function setName(?SalesChannelEntity $salesChannelEntity = null, $name): void
    {
        $this->configService->setName($salesChannelEntity, $name);
    }

    /**
     * @param SalesChannelEntity|null $salesChannelEntity
     * @param $status
     */
    public function setStatus(?SalesChannelEntity $salesChannelEntity = null, $status): void
    {
        $this->configService->setStatus($salesChannelEntity, $status);
    }
}
