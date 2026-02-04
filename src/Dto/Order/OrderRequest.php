<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Order;

use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class OrderRequest
{
    public function __construct(private string $orderId)
    {
        if ($this->orderId === '') {
            throw new InvalidRequestException('OrderId must not be empty.');
        }
    }

    public function orderId(): string
    {
        return $this->orderId;
    }
}
