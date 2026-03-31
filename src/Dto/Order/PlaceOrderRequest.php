<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Order;

final class PlaceOrderRequest
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private string $accountId,
        private array $payload,
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
    public function toPayload(): array
    {
        return $this->payload;
    }
}
