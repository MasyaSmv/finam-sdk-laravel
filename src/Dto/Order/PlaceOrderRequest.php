<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Order;

final class PlaceOrderRequest
{
    public function __construct(
        private string $accountId,
        private PlaceOrderInputDto $payload,
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
     * @return array<string, string>
     */
    public function toPayload(): array
    {
        return $this->payload->toPayload();
    }
}
