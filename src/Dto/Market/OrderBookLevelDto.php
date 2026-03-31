<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Market;

final class OrderBookLevelDto
{
    public function __construct(
        private string $price,
        private string $quantity,
    ) {
    }

    public function price(): string
    {
        return $this->price;
    }

    public function quantity(): string
    {
        return $this->quantity;
    }
}
