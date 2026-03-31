<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Order;

final class OrdersRequest
{
    public function __construct(
        private string $accountId,
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
     * @return array<never, never>
     */
    public function toQuery(): array
    {
        return [];
    }
}
