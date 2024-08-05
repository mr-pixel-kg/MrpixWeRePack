<?php

namespace Mrpix\WeRepack\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfigService
{
    public const SYSTEM_CONFIG_DOMAIN = 'MrpixWeRepack.config.';
    protected SystemConfigService $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    public function get(string $key, ?string $salesChannelId = null)
    {
        return $this->getSystemConfigService()->get(self::SYSTEM_CONFIG_DOMAIN . $key, $salesChannelId);
    }

    public function getSystemConfigService(): SystemConfigService
    {
        return $this->systemConfigService;
    }

    public function set(string $key, string $value, ?string $salesChannelId = null): void
    {
        $this->getSystemConfigService()->set(self::SYSTEM_CONFIG_DOMAIN . $key, $value, $salesChannelId);
    }

    public function delete(string $key, ?string $salesChannelId = null): void
    {
        $this->getSystemConfigService()->delete(self::SYSTEM_CONFIG_DOMAIN . $key, $salesChannelId);
    }
}
