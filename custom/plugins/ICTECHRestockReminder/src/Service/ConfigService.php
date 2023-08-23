<?php

declare(strict_types=1);

namespace ICTECHRestockReminder\Service;

use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Class ConfigService
 *
 * @package ICTECHRestockReminder\Service
 */
class ConfigService
{
    protected SystemConfigService $configService;

    public function __construct(
        SystemConfigService $configService
    ) {
        $this->configService = $configService;
    }


    public function getIsActive(): ?bool
    {
        /** @var bool $isActive */
        $isActive = $this->configService->get('ICTECHRestockReminder.restock.active');
        return !is_null($isActive) ? $isActive : false;
    }


    public function getInterval(?SalesChannelEntity $salesChannelEntity = null): int
    {
        $interval = $this->configService->get(
            'ICTECHRestockReminder.restock.interval',
            $salesChannelEntity ? $salesChannelEntity->getId() : null
        );
        return !is_null($interval) ? (int) $interval : 0;
    }

    public function setName(
        ?SalesChannelEntity $salesChannelEntity = null,
        string $name
    ): void {
        $this->configService->set(
            'ICTECHRestockReminder.restock.name',
            $name,
            $salesChannelEntity ? $salesChannelEntity->getId() : null
        );
    }

    public function setStatus(
        ?SalesChannelEntity $salesChannelEntity = null,
        string $status
    ): void {
        $this->configService->set(
            'ICTECHRestockReminder.restock.status',
            $status,
            $salesChannelEntity ? $salesChannelEntity->getId() : null
        );
    }

}
