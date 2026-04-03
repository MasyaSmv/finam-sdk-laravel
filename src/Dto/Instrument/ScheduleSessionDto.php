<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Instrument;

use DateTimeImmutable;

final class ScheduleSessionDto
{
    public function __construct(
        private string $type,
        private DateTimeImmutable $startAt,
        private DateTimeImmutable $endAt,
    ) {
    }

    public function type(): string
    {
        return $this->type;
    }

    public function startAt(): DateTimeImmutable
    {
        return $this->startAt;
    }

    public function endAt(): DateTimeImmutable
    {
        return $this->endAt;
    }
}
