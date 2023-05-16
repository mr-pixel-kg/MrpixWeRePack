<?php

namespace Mrpix\WeRepack\Service\TelemetryService;

interface TelemetryServiceInterface
{

    public function sendTelemetryData(string $url, string $language = 'en');

    public function send(TelemetryPacket $telemetryPacket);

    public function synchronize();

}