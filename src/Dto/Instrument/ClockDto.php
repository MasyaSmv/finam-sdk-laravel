<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Instrument;

use DateTimeImmutable;

final class ClockDto
{
    public function __construct(private DateTimeImmutable $timestamp)
    {
    }

    public function timestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }
}
