<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Collections;

use Illuminate\Support\Collection;
use MasyaSmv\FinamSdk\Dto\Market\TradeDto;

final class TradeCollection extends Collection
{
    /**
     * @param list<TradeDto> $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }
}
