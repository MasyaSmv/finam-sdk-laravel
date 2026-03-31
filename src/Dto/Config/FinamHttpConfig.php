<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Config;

final class FinamHttpConfig
{
    public function __construct(
        private float $timeout,
        private float $connectTimeout,
        private int $retries,
        private int $retryDelayMs,
        private string $userAgent,
    ) {
    }

    public function timeout(): float
    {
        return $this->timeout;
    }

    public function connectTimeout(): float
    {
        return $this->connectTimeout;
    }

    public function retries(): int
    {
        return $this->retries;
    }

    public function retryDelayMs(): int
    {
        return $this->retryDelayMs;
    }

    public function userAgent(): string
    {
        return $this->userAgent;
    }
}
