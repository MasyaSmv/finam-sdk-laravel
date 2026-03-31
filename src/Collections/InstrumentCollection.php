<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Collections;

use Illuminate\Support\Collection;
use MasyaSmv\FinamSdk\Dto\Instrument\InstrumentDto;

final class InstrumentCollection extends Collection
{
    /**
     * @param list<InstrumentDto> $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }

    public function findBySymbol(string $symbol): ?InstrumentDto
    {
        foreach ($this->items as $instrument) {
            if ($instrument->symbol() === $symbol) {
                return $instrument;
            }
        }

        return null;
    }
}
