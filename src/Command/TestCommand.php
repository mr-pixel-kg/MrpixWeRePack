<?php

namespace Mrpix\WeRepack\Command;

use Mrpix\WeRepack\Service\WeRepackTelemetryService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestCommand extends Command
{
    protected static string $defaultName = 'mrpixwerepack:test';

    public function __construct(private readonly WeRepackTelemetryService $telemetryService)
    {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title("WeRepack Test");

        $url = $io->ask('What is your shop url?');
        $language = $io->ask('What is your shop language?', 'en');

        $this->telemetryService->sendTelemetryData($url, $language);

        return Command::SUCCESS;
    }
}
