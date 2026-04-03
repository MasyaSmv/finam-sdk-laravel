<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Collections;

use Illuminate\Support\Collection;
use MasyaSmv\FinamSdk\Dto\Market\QuoteDto;

final class QuoteCollection extends Collection
{
    /**
     * @param list<QuoteDto> $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }

    public function findBySymbol(string $symbol): ?QuoteDto
    {
        foreach ($this->items as $quote) {
            if ($quote->symbol() === $symbol) {
                return $quote;
            }
        }

        return null;
    }
}
