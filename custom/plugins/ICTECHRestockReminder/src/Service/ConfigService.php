<?php

namespace ICTECHRestockReminder\Service;

use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Class ConfigService
 *
 * @package ICTECHRestockReminder\Service
 */
class ConfigService {

    /**
     * @var SystemConfigService
     */
    protected SystemConfigService $configService;

    /**
     * ConfigService constructor.
     * @param SystemConfigService $configService
     */
    public function __construct(
        SystemConfigService $configService
    ) {
        $this->configService = $configService;
    }

    /**
     * @return bool|null
     */
    public function getIsActive(): ?bool
    {
        /** @var bool $isActive */
        $isActive = $this->configService->get('ICTECHRestockReminder.restock.active');
        return !is_null($isActive) ? $isActive : false;
    }

    /**
     * @return int
     */
    public function getInterval(?SalesChannelEntity $salesChannelEntity = null): int
    {
        $interval = $this->configService->get(
            'ICTECHRestockReminder.restock.interval',
            $salesChannelEntity ? $salesChannelEntity->getId():null);
        return !is_null($interval) ? $interval : 0;
    }

    public function setName(?SalesChannelEntity $salesChannelEntity = null, $name): void
    {
        $this->configService->set(
            'ICTECHRestockReminder.restock.name',
            $name,
            $salesChannelEntity ? $salesChannelEntity->getId() : null
        );
    }

    public function setStatus(?SalesChannelEntity $salesChannelEntity = null, $status): void
    {
        $this->configService->set(
            'ICTECHRestockReminder.restock.status',
            $status,
            $salesChannelEntity ? $salesChannelEntity->getId() : null
        );
    }

}
