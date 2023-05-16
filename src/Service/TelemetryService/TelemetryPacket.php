<?php

namespace Mrpix\WeRepack\Service\TelemetryService;

class TelemetryPacket
{
    private int $repack_last_sent;
    private bool $repack_coupon;
    private float $repack_ratio;
    private int $repack_counter;
    private int $repack_start;
    private string $site_lang;
    private string $site_url;

    public function __construct(bool $repack_coupon, float $repack_ratio, int $repack_counter, int $repack_start, string $site_lang, string $site_url)
    {
        $this->repack_last_sent = time();
        $this->repack_coupon = $repack_coupon;
        $this->repack_ratio = $repack_ratio;
        $this->repack_counter = $repack_counter;
        $this->repack_start = $repack_start;
        $this->site_lang = $site_lang;
        $this->site_url = $site_url;
    }

    public function toArray(): array
    {
        return [
            'repack_last_sent' => $this->repack_last_sent,
            'repack_coupon' => ($this->repack_coupon) ? '1' : '0',
            'repack_ratio' => $this->repack_ratio,
            'repack_counter' => $this->repack_counter,
            'repack_start' => $this->repack_start,
            'site_lang' => $this->site_lang,
            'site_url' => $this->site_url
        ];
    }

    public function getRepackLastSent(): int
    {
        return $this->repack_last_sent;
    }

    public function setRepackLastSent(int $repack_last_sent): void
    {
        $this->repack_last_sent = $repack_last_sent;
    }

    public function isRepackCoupon(): bool
    {
        return $this->repack_coupon;
    }

    public function setRepackCoupon(bool $repack_coupon): void
    {
        $this->repack_coupon = $repack_coupon;
    }

    public function getRepackRatio(): float
    {
        return $this->repack_ratio;
    }

    public function setRepackRatio(float $repack_ratio): void
    {
        $this->repack_ratio = $repack_ratio;
    }

    public function getRepackCounter(): int
    {
        return $this->repack_counter;
    }

    public function setRepackCounter(int $repack_counter): void
    {
        $this->repack_counter = $repack_counter;
    }

    public function getRepackStart(): int
    {
        return $this->repack_start;
    }

    public function setRepackStart(int $repack_start): void
    {
        $this->repack_start = $repack_start;
    }

    public function getSiteLang(): string
    {
        return $this->site_lang;
    }

    public function setSiteLang(string $site_lang): void
    {
        $this->site_lang = $site_lang;
    }

    public function getSiteUrl(): string
    {
        return $this->site_url;
    }

    public function setSiteUrl(string $site_url): void
    {
        $this->site_url = $site_url;
    }

}