<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts;

use DateTimeInterface;
use MasyaSmv\FinamSdk\Collections\OperationCollection;
use MasyaSmv\FinamSdk\Dto\Connect\SessionDetailsDto;

interface FinamSessionInterface
{
    public function sessionDetails(): SessionDetailsDto;

    public function getOperationsByDate(
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
        ?string $accountId = null,
        ?int $limit = null,
    ): OperationCollection;
}
