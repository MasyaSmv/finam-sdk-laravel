<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Instrument;

final class ExchangesRequest
{
    public function __construct()
    {
    }

    /**
     * @return array<string, scalar>
     */
    public function toQuery(): array
    {
        return [];
    }
}
