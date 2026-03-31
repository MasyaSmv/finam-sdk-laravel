<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Collections;

use Illuminate\Support\Collection;
use MasyaSmv\FinamSdk\Dto\Market\CandleDto;

final class CandleCollection extends Collection
{
    /**
     * @param list<CandleDto> $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }
}
