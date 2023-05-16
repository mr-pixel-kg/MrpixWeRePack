<?php

namespace Mrpix\WeRepack\Service\TelemetryService;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Mrpix\WeRepack\Repository\WeRepackOrderRepository;
use Mrpix\WeRepack\Service\ConfigService;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;

class TelemetryService implements TelemetryServiceInterface
{
    const ENDPOINT_URL = 'https://werepack.org/api/community/v1/sites';

    protected Client $client;
    protected WeRepackOrderRepository $weRepackOrderRepository;
    protected LoggerInterface $logger;
    protected ConfigService $configService;

    public function __construct(WeRepackOrderRepository $weRepackOrderRepository, ConfigService $configService, LoggerInterface $logger)
    {
        $this->client = new Client([
            'timeout' => 2.0,
        ]);
        $this->weRepackOrderRepository = $weRepackOrderRepository;
        $this->configService = $configService;
        $this->logger = $logger;
    }

    public function sendTelemetryData(string $url, string $language = 'en'): void
    {
        $context = Context::createDefaultContext();

        $telemetryPacket = new TelemetryPacket(
            $this->configService->get('createPromotionCodes'),
            $this->weRepackOrderRepository->getWeRepackRatio($context),
            $this->weRepackOrderRepository->getWeRepackOrderCount($context),
            $this->weRepackOrderRepository->getWeRepackStart($context)->getTimestamp(),
            $language,
            $url
        );

        $this->send($telemetryPacket);
    }

    public function send(TelemetryPacket $telemetryPacket): void
    {
        try {
            $response = $this->client->request('POST', self::ENDPOINT_URL, [
                'form_params' => $telemetryPacket->toArray()
            ]);
            $this->logger->info('Successfully transferred WeRepack telemetry data.', ['data' => $telemetryPacket->toArray(), 'response' => $response]);
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to send WeRepack telemetry data.', ['exception' => $e]);
        }
    }

    public function synchronize()
    {
        // TODO: Implement synchronize() method.
    }
}