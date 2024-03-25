<?php

namespace Mrpix\WeRepack\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Mrpix\WeRepack\Repository\WeRepackOrderRepository;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;

class WeRepackTelemetryService
{
    public const ENDPOINT_URL = 'https://werepack.org/api/community/v1/sites';

    protected Client $client;

    public function __construct(protected WeRepackOrderRepository $weRepackOrderRepository, protected ConfigService $configService, protected LoggerInterface $logger)
    {
        $this->client = new Client([
            'timeout' => 2.0,
        ]);
    }

    public function sendTelemetryData(string $url, string $language = 'en'): void
    {
        $context = Context::createDefaultContext();
        $data = [
            'repack_last_sent' => time(),
            'repack_coupon' => ($this->configService->get('createPromotionCodes')) ? '1' : '0',
            'repack_ratio' => $this->weRepackOrderRepository->getWeRepackRatio($context),
            'repack_counter' => $this->weRepackOrderRepository->getWeRepackOrderCount($context),
            'repack_start' => $this->weRepackOrderRepository->getWeRepackStart($context)->getTimestamp(),
            'site_lang' => $language,
            'site_url' => $url
        ];

        try {
            $response = $this->client->request('POST', self::ENDPOINT_URL, [
                'form_params' => $data
            ]);
            $this->logger->info('Successfully transferred WeRepack telemetry data.', ['data' => $data, 'response' => $response]);
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to send WeRepack telemetry data.', ['exception' => $e]);
        }
    }

}