<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Collections;

use Illuminate\Support\Collection;
use MasyaSmv\FinamSdk\Dto\Instrument\ExchangeDto;

final class ExchangeCollection extends Collection
{
    /**
     * @param list<ExchangeDto> $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }

    public function findByMic(string $mic): ?ExchangeDto
    {
        foreach ($this->items as $exchange) {
            if ($exchange->mic() === $mic) {
                return $exchange;
            }
        }

        return null;
    }
}
