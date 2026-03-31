<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Order;

final class OrdersRequest
{
    /**
     * @param array<string, mixed> $query
     */
    public function __construct(
        private string $accountId,
        private array $query = [],
    )
    {
        if ($this->accountId === '') {
            throw new \MasyaSmv\FinamSdk\Exceptions\InvalidRequestException('AccountId must not be empty.');
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
        return $this->query;
    }
}
