<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Order;

use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class PlaceOrderInputDto
{
    public function __construct(
        private string $symbol,
        private string $quantity,
        private string $side,
        private string $type,
        private string $timeInForce,
        private ?string $limitPrice = null,
        private ?string $stopPrice = null,
        private ?string $stopCondition = null,
        private ?string $clientOrderId = null,
        private ?string $comment = null,
    ) {
        if ($this->symbol === '') {
            throw new InvalidRequestException('Symbol must not be empty.');
        }

        if ($this->quantity === '') {
            throw new InvalidRequestException('Quantity must not be empty.');
        }

        if ($this->side === '') {
            throw new InvalidRequestException('Side must not be empty.');
        }

        if ($this->type === '') {
            throw new InvalidRequestException('Type must not be empty.');
        }

        if ($this->timeInForce === '') {
            throw new InvalidRequestException('TimeInForce must not be empty.');
        }
    }

    /**
     * @return array<string, string>
     */
    public function toPayload(): array
    {
        $payload = [
            'symbol' => $this->symbol,
            'quantity' => $this->quantity,
            'side' => $this->side,
            'type' => $this->type,
            'time_in_force' => $this->timeInForce,
        ];

        if ($this->limitPrice !== null) {
            $payload['limit_price'] = $this->limitPrice;
        }

        if ($this->stopPrice !== null) {
            $payload['stop_price'] = $this->stopPrice;
        }

        if ($this->stopCondition !== null) {
            $payload['stop_condition'] = $this->stopCondition;
        }

        if ($this->clientOrderId !== null) {
            $payload['client_order_id'] = $this->clientOrderId;
        }

        if ($this->comment !== null) {
            $payload['comment'] = $this->comment;
        }

        return $payload;
    }
}
