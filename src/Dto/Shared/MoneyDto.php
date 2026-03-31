<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Shared;

final class MoneyDto
{
    public function __construct(
        private string $currencyCode,
        private string $units,
        private int $nanos,
    ) {
    }

    public function currencyCode(): string
    {
        return $this->currencyCode;
    }

    public function units(): string
    {
        return $this->units;
    }

    public function nanos(): int
    {
        return $this->nanos;
    }
}
