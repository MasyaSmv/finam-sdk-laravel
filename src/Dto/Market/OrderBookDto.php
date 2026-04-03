<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Market;

use MasyaSmv\FinamSdk\Collections\OrderBookRowCollection;

final class OrderBookDto
{
    public function __construct(
        private string $symbol,
        private OrderBookRowCollection $rows,
    ) {
    }

    public function symbol(): string
    {
        return $this->symbol;
    }

    public function rows(): OrderBookRowCollection
    {
        return $this->rows;
    }

    public function buyRows(): OrderBookRowCollection
    {
        /** @var list<OrderBookRowDto> $rows */
        $rows = $this->rows
            ->filter(static fn (OrderBookRowDto $row): bool => $row->buySize() !== '0')
            ->values()
            ->all();

        return new OrderBookRowCollection($rows);
    }

    public function sellRows(): OrderBookRowCollection
    {
        /** @var list<OrderBookRowDto> $rows */
        $rows = $this->rows
            ->filter(static fn (OrderBookRowDto $row): bool => $row->sellSize() !== '0')
            ->values()
            ->all();

        return new OrderBookRowCollection($rows);
    }
}
