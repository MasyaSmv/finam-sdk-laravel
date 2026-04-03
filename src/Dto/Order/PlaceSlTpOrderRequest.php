<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Order;

use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class PlaceSlTpOrderRequest
{
    public function __construct(
        private string $accountId,
        private PlaceSlTpOrderInputDto $payload,
    ) {
        if ($this->accountId === '') {
            throw new InvalidRequestException('Account ID must not be empty.');
        }
    }

    public function accountId(): string
    {
        return $this->accountId;
    }

    public function payload(): PlaceSlTpOrderInputDto
    {
        return $this->payload;
    }

    /**
     * @return array<string, string|array{value: string}>
     */
    public function toPayload(): array
    {
        return $this->payload->toPayload();
    }
}
