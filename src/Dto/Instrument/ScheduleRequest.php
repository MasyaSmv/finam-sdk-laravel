<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Instrument;

use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class ScheduleRequest
{
    public function __construct(private string $symbol)
    {
        if ($this->symbol === '') {
            throw new InvalidRequestException('Symbol must not be empty.');
        }
    }

    /**
     * @return array<string, string>
     */
    public function toQuery(): array
    {
        return [
            'symbol' => $this->symbol,
        ];
    }
}
