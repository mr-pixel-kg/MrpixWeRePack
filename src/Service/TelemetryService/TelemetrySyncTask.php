<?php

namespace Mrpix\WeRepack\Service\TelemetryService;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class TelemetrySyncTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'mrpix.werepack_sync';
    }

    public static function getDefaultInterval(): int
    {
        return 86400; // 24 hours
    }
}