<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Order;

use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class CancelOrderRequest
{
    public function __construct(
        private string $accountId,
        private string $orderId,
    ) {
        if ($this->accountId === '') {
            throw new InvalidRequestException('AccountId must not be empty.');
        }

        if ($this->orderId === '') {
            throw new InvalidRequestException('OrderId must not be empty.');
        }
    }

    public function accountId(): string
    {
        return $this->accountId;
    }

    public function orderId(): string
    {
        return $this->orderId;
    }

    /**
     * @return array<never, never>
     */
    public function toPayload(): array
    {
        return [];
    }
}
