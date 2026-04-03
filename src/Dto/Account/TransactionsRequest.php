<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Account;

use MasyaSmv\FinamSdk\Dto\Shared\Interval;
use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class TransactionsRequest
{
    public function __construct(
        private string $accountId,
        private ?int $limit = null,
        private ?Interval $interval = null,
    ) {
        if ($this->accountId === '') {
            throw new InvalidRequestException('AccountId must not be empty.');
        }

        if ($this->limit !== null && $this->limit <= 0) {
            throw new InvalidRequestException('Limit must be greater than zero.');
        }
    }

    public function accountId(): string
    {
        return $this->accountId;
    }

    /**
     * @return array<string, mixed>
     */
    public function toQuery(): array
    {
        $query = [];

        if ($this->limit !== null) {
            $query['limit'] = $this->limit;
        }

        if ($this->interval !== null) {
            $query = array_merge($query, $this->interval->toRestQuery());
        }

        return $query;
    }
}
