<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Market;

use MasyaSmv\FinamSdk\Collections\OrderBookLevelCollection;

final class OrderBookDto
{
    public function __construct(
        private string $symbol,
        private OrderBookLevelCollection $bids,
        private OrderBookLevelCollection $asks,
    ) {
    }

    public function symbol(): string
    {
        return $this->symbol;
    }

    public function bids(): OrderBookLevelCollection
    {
        return $this->bids;
    }

    public function asks(): OrderBookLevelCollection
    {
        return $this->asks;
    }
}
