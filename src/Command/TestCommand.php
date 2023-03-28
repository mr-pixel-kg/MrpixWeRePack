<?php

namespace Mrpix\WeRepack\Command;

use Mrpix\WeRepack\Service\WeRepackTelemetryService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestCommand extends Command
{
    protected static $defaultName = 'mrpixwerepack:test';

    private EntityRepository $werepackOrderRepository;
    private EntityRepository $orderRepository;
    private WeRepackTelemetryService $telemetryService;

    public function __construct(EntityRepository $werepackOrderRepository, EntityRepository $orderRepository, WeRepackTelemetryService $telemetryService)
    {
        parent::__construct(null);
        $this->werepackOrderRepository = $werepackOrderRepository;
        $this->orderRepository = $orderRepository;
        $this->telemetryService = $telemetryService;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title("WeRepack Test");

        $url = $io->ask('What is your shop url?');
        $language = $io->ask('What is your shop language?', 'en');

        $this->telemetryService->sendTelemetryData($url, $language);

        return Command::SUCCESS;
    }
}