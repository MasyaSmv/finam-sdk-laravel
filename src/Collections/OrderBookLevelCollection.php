<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Collections;

use Illuminate\Support\Collection;
use MasyaSmv\FinamSdk\Dto\Market\OrderBookLevelDto;

final class OrderBookLevelCollection extends Collection
{
    /**
     * @param list<OrderBookLevelDto> $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }
}
