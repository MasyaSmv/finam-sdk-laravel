<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Collections;

use Illuminate\Support\Collection;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleSessionDto;

final class ScheduleSessionCollection extends Collection
{
    /**
     * @param list<ScheduleSessionDto> $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }

    public function firstByType(string $type): ?ScheduleSessionDto
    {
        foreach ($this->items as $session) {
            if ($session->type() === $type) {
                return $session;
            }
        }

        return null;
    }
}
