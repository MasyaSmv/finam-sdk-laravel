<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Instrument;

use MasyaSmv\FinamSdk\Collections\ScheduleSessionCollection;

final class ScheduleDto
{
    public function __construct(
        private string $symbol,
        private ScheduleSessionCollection $sessions,
    ) {
    }

    public function symbol(): string
    {
        return $this->symbol;
    }

    public function sessions(): ScheduleSessionCollection
    {
        return $this->sessions;
    }
}
