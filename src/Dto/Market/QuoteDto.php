<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Market;

use DateTimeImmutable;

final class QuoteDto
{
    public function __construct(
        private string $symbol,
        private string $price,
        private ?string $change = null,
        private ?string $percentChange = null,
        private ?DateTimeImmutable $timestamp = null,
    ) {
    }

    public function symbol(): string
    {
        return $this->symbol;
    }

    public function price(): string
    {
        return $this->price;
    }

    public function change(): ?string
    {
        return $this->change;
    }

    public function percentChange(): ?string
    {
        return $this->percentChange;
    }

    public function timestamp(): ?DateTimeImmutable
    {
        return $this->timestamp;
    }
}
