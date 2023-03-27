<?php

namespace Mrpix\WeRepack\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Mrpix\WeRepack\Repository\WeRepackOrderRepository;
use Shopware\Core\Framework\Context;

class WeRepackTelemetryService
{
    const ENDPOINT_URL = 'https://werepack.org/api/community/v1/sites';

    private Client $client;
    private WeRepackOrderRepository $weRepackOrderRepository;

    public function __construct(WeRepackOrderRepository $weRepackOrderRepository) {
        $this->client = new Client([
            'timeout'  => 2.0,
        ]);
        $this->weRepackOrderRepository = $weRepackOrderRepository;
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
        } catch (GuzzleException $e) {
            // todo log exeption
            throw $e;
        }

        dump($data, $response);
    }

}