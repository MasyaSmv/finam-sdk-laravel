<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Shared;

use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class Interval
{
    public function __construct(
        private int $start,
        private int $end,
    ) {
        if ($this->start <= 0 || $this->end <= 0) {
            throw new InvalidRequestException('Interval start/end must be positive Unix timestamps.');
        }

        if ($this->start > $this->end) {
            throw new InvalidRequestException('Interval start must be less than or equal to end.');
        }
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'start' => $this->start,
            'end' => $this->end,
        ];
    }
}
