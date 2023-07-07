<?php

declare(strict_types=1);

namespace ICTECHPropertyMigration\Controller;

use mysqli;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class CommonMySQLController
{
    private SystemConfigService $systemConfigService;

    public function __construct(
        SystemConfigService $systemConfigService,
    ) {
        $this->systemConfigService = $systemConfigService;
    }

    // connect with database
    public function getMySqlConnection(): mysqli
    {
        $hostname = $this->systemConfigService
            ->get('ICTECHPropertyMigration.config.databaseHost');
        $dbName = $this->systemConfigService
            ->get('ICTECHPropertyMigration.config.databaseName');
        $dbUsername = $this->systemConfigService
            ->get('ICTECHPropertyMigration.config.databaseUserName');
        $dbPassword = $this->systemConfigService
            ->get('ICTECHPropertyMigration.config.databasePassword');

        return new mysqli($hostname, $dbUsername, $dbPassword, $dbName);
    }
}
