<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Collections;

use DateTimeInterface;
use Illuminate\Support\Collection;
use MasyaSmv\FinamSdk\Dto\Account\OperationDto;

final class OperationCollection extends Collection
{
    /**
     * @param list<OperationDto> $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }

    public function findById(string $operationId): ?OperationDto
    {
        foreach ($this->items as $operation) {
            if ($operation->id() === $operationId) {
                return $operation;
            }
        }

        return null;
    }

    public function between(DateTimeInterface $startDate, DateTimeInterface $endDate): self
    {
        /** @var list<OperationDto> $items */
        $items = array_values(array_filter(
            $this->items,
            static fn (OperationDto $operation): bool => $operation->occurredAt() >= $startDate
                && $operation->occurredAt() <= $endDate,
        ));

        return new self($items);
    }
}
