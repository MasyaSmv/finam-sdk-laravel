<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Market;

use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class OrderbookRequest
{
    public function __construct(private string $symbol)
    {
        if ($this->symbol === '') {
            throw new InvalidRequestException('Symbol must not be empty.');
        }
    }

    public function symbol(): string
    {
        return $this->symbol;
    }

    /**
     * @return array<never, never>
     */
    public function toQuery(): array
    {
        return [];
    }
}
