<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Market;

final class CandlesRequest
{
    public function __construct(private CandlesQueryDto $query)
    {
    }

    public function symbol(): string
    {
        return $this->query->symbol();
    }

    /**
     * @return array{timeframe: string, 'interval.startTime': string, 'interval.endTime': string}
     */
    public function toQuery(): array
    {
        return array_merge([
            'timeframe' => $this->query->timeframe(),
        ], $this->query->interval()->toRestQuery());
    }
}
