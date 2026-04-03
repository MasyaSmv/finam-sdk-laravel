<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Collections;

use Illuminate\Support\Collection;
final class StringCollection extends Collection
{
    /**
     * @param list<string> $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }

    /**
     * @return list<string>
     */
    public function strings(): array
    {
        /** @var list<string> $items */
        $items = $this->items;

        return $items;
    }
}
