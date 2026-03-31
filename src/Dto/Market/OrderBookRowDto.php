<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Market;

use DateTimeImmutable;

final class OrderBookRowDto
{
    public function __construct(
        private string $price,
        private string $sellSize,
        private string $buySize,
        private ?string $action = null,
        private ?string $mpid = null,
        private ?DateTimeImmutable $timestamp = null,
    ) {
    }

    public function price(): string
    {
        return $this->price;
    }

    public function sellSize(): string
    {
        return $this->sellSize;
    }

    public function buySize(): string
    {
        return $this->buySize;
    }

    public function action(): ?string
    {
        return $this->action;
    }

    public function mpid(): ?string
    {
        return $this->mpid;
    }

    public function timestamp(): ?DateTimeImmutable
    {
        return $this->timestamp;
    }
}
