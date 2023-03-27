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

    public function __construct(WeRepackOrderRepository $weRepackOrderRepository, LoggerInterface $logger) {
        $this->client = new Client([
            'timeout'  => 2.0,
        ]);
        $this->weRepackOrderRepository = $weRepackOrderRepository;
        $this->logger = $logger;
    }

    public function sendTelemetryData(): void {
        $data = [
            'repack_last_sent' => time(),
            'repack_coupon' => 1,
            'repack_ratio' => $this->weRepackOrderRepository->getWeRepackRatio(Context::createDefaultContext()),
            'repack_counter' => $this->weRepackOrderRepository->getWeRepackOrderCount(Context::createDefaultContext()),
            'repack_start' => $this->weRepackOrderRepository->getWeRepackStart(Context::createDefaultContext())->getTimestamp(),
            'site_lang' => 'de',
            'site_url' => 'https://shop.mr-pixel.de'
        ];

        try {
            $response = $this->client->request('POST', self::ENDPOINT_URL, [
                'form_params' => $data
            ]);
            $this->logger->info('Successfully transferred WeRepack telemetry data.');
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to send WeRepack telemetry data.', ['exception' => $e]);
        }
    }

}