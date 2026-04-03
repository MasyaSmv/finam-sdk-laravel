<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Collections\Transport;

use Illuminate\Support\Collection;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
final class ApiPayloadCollection extends Collection
{
    /**
     * @param list<ApiPayload> $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }

    /**
     * @return list<ApiPayload>
     */
    public function payloads(): array
    {
        /** @var list<ApiPayload> $items */
        $items = $this->items;

        return $items;
    }
}
