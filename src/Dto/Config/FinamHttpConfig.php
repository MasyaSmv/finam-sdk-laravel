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
        private float $retryMultiplier = 1.0,
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

    /**
     * Множитель экспоненциальной задержки между повторами (delay *= multiplier^n).
     * Значение 1.0 даёт линейную (фиксированную) задержку.
     */
    public function retryMultiplier(): float
    {
        return $this->retryMultiplier;
    }
}
