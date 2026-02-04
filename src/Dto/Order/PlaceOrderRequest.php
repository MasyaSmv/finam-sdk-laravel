<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Order;

final class PlaceOrderRequest
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(private array $payload)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return $this->payload;
    }
}
