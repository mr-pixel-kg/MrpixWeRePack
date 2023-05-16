<?php

namespace Mrpix\WeRepack\Service\TelemetryService;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class TelemetrySyncTaskHandler extends ScheduledTaskHandler
{
    private TelemetryServiceInterface $telemetryService;

    public function __construct(EntityRepository $scheduledTaskRepository, TelemetryServiceInterface $telemetryService)
    {
        parent::__construct($scheduledTaskRepository);
        $this->telemetryService = $telemetryService;
    }

    public static function getHandledMessages(): iterable
    {
        return [ TelemetrySyncTask::class ];
    }

    public function run(): void
    {
        $this->telemetryService->synchronize();
    }
}