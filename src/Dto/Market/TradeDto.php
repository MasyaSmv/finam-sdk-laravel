<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Market;

use DateTimeImmutable;

final class TradeDto
{
    public function __construct(
        private string $symbol,
        private string $price,
        private string $size,
        private ?DateTimeImmutable $timestamp = null,
        private ?string $side = null,
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

    public function size(): string
    {
        return $this->size;
    }

    public function timestamp(): ?DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function side(): ?string
    {
        return $this->side;
    }
}
