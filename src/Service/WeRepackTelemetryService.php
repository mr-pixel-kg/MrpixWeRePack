<?php

namespace Mrpix\WeRepack\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Mrpix\WeRepack\Repository\WeRepackOrderRepository;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;

class WeRepackTelemetryService
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

    public function sendTelemetryData(string $url, string $language='en'): void
    {
        $data = [
            'repack_last_sent' => time(),
            'repack_coupon' => ($this->configService->get('createPromotionCodes'))?'1':'0',
            'repack_ratio' => $this->weRepackOrderRepository->getWeRepackRatio(Context::createDefaultContext()),
            'repack_counter' => $this->weRepackOrderRepository->getWeRepackOrderCount(Context::createDefaultContext()),
            'repack_start' => $this->weRepackOrderRepository->getWeRepackStart(Context::createDefaultContext())->getTimestamp(),
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