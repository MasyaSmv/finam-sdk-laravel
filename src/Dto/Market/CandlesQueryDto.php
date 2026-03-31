<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Market;

use DateTimeInterface;
use MasyaSmv\FinamSdk\Dto\Shared\Interval;
use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class CandlesQueryDto
{
    public function __construct(
        private string $symbol,
        private string $timeframe,
        private DateTimeInterface $startDate,
        private DateTimeInterface $endDate,
    ) {
        if ($this->symbol === '') {
            throw new InvalidRequestException('Symbol must not be empty.');
        }

        if ($this->timeframe === '') {
            throw new InvalidRequestException('Timeframe must not be empty.');
        }

        if ($this->startDate > $this->endDate) {
            throw new InvalidRequestException('Start date must be less than or equal to end date.');
        }
    }

    /**
     * @return array<string, string|array<string, int>>
     */
    public function toQuery(): array
    {
        return [
            'symbol' => $this->symbol,
            'timeframe' => $this->timeframe,
            'interval' => (new Interval($this->startDate->getTimestamp(), $this->endDate->getTimestamp()))->toArray(),
        ];
    }
}
