<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Account;

final class OperationTradeDto
{
    public function __construct(
        private string $tradeId,
        private ?string $orderId = null,
    ) {
    }

    public function tradeId(): string
    {
        return $this->tradeId;
    }

    public function orderId(): ?string
    {
        return $this->orderId;
    }
}
