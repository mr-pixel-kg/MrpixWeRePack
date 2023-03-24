<?php

namespace Mrpix\WeRepack\Service;

use GuzzleHttp\Client;

class WeRepackTelemetryService
{
    const ENDPOINT_URL = 'https://werepack.org/api/community/v1/sites';

    private Client $client;

    public function __construct() {
        $this->client = new Client([
            'timeout'  => 2.0,
        ]);
    }

    public function sendTelemetryData(): void {

        $data = [
            'repack_last_sent' => 0,
            'repack_coupon' => 1,
            'repack_ratio' => 0.70,
            'repack_counter' => 1,
            'repack_start' => 0,
            'site_lang' => 'de',
            'site_url' => 'https://shop.mr-pixel.de'
        ];

        $response = $this->client->request('POST', self::ENDPOINT_URL, [
            'form_params' => $data
        ]);

        dump($response);
    }

}