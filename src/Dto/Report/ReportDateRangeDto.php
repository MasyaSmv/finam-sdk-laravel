<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Report;

use DateTimeInterface;
use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class ReportDateRangeDto
{
    public function __construct(
        private DateTimeInterface $from,
        private DateTimeInterface $to,
    ) {
        if ($this->from > $this->to) {
            throw new InvalidRequestException('Report date range start must be less than or equal to end.');
        }
    }

    public function from(): DateTimeInterface
    {
        return $this->from;
    }

    public function to(): DateTimeInterface
    {
        return $this->to;
    }

    /**
     * @return array{from: string, to: string}
     */
    public function toPayload(): array
    {
        return [
            'from' => $this->from->format('Y-m-d'),
            'to' => $this->to->format('Y-m-d'),
        ];
    }
}
