<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Market;

use DateTimeImmutable;

final class CandleDto
{
    public function __construct(
        private DateTimeImmutable $timestamp,
        private string $open,
        private string $high,
        private string $low,
        private string $close,
        private ?string $volume = null,
    ) {
    }

    public function timestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function open(): string
    {
        return $this->open;
    }

    public function high(): string
    {
        return $this->high;
    }

    public function low(): string
    {
        return $this->low;
    }

    public function close(): string
    {
        return $this->close;
    }

    public function volume(): ?string
    {
        return $this->volume;
    }
}
