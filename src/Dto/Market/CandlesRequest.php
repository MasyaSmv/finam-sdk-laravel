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
     * @return array{timeframe: string, interval: array<string, int>}
     */
    public function toQuery(): array
    {
        return [
            'timeframe' => $this->query->timeframe(),
            'interval' => $this->query->interval()->toArray(),
        ];
    }
}
