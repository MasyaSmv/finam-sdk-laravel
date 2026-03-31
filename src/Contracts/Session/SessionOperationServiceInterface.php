<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Session;

use DateTimeInterface;
use MasyaSmv\FinamSdk\Collections\OperationCollection;

interface SessionOperationServiceInterface
{
    public function getOperationsByDate(
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
        ?string $accountId = null,
        ?int $limit = null,
    ): OperationCollection;
}
