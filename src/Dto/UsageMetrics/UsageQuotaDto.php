<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\UsageMetrics;

final class UsageQuotaDto
{
    public function __construct(
        private string $name,
        private string $limit,
        private string $remaining,
        private string $resetTime,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function limit(): string
    {
        return $this->limit;
    }

    public function remaining(): string
    {
        return $this->remaining;
    }

    public function resetTime(): string
    {
        return $this->resetTime;
    }
}
