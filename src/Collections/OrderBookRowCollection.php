<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Collections;

use Illuminate\Support\Collection;
use MasyaSmv\FinamSdk\Dto\Market\OrderBookRowDto;

final class OrderBookRowCollection extends Collection
{
    /**
     * @param list<OrderBookRowDto> $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }
}
