<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Collections\OrderCollection;
use MasyaSmv\FinamSdk\Dto\Order\OrderDto;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;
final class OrderMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    public function mapCollection(ApiPayload $data, string $accountId): OrderCollection
    {
        $orders = [];

        foreach ($this->reader->requireObjectList($data, 'orders')->payloads() as $orderData) {
            $orders[] = $this->map($orderData, $accountId);
        }

        /** @var list<OrderDto> $orders */
        return new OrderCollection($orders);
    }

    public function map(ApiPayload $data, string $accountId): OrderDto
    {
        $orderData = $this->reader->optionalObject($data, 'order') ?? $data;

        return new OrderDto(
            orderId: $this->reader->requireString($orderData, 'order_id'),
            execId: $this->reader->optionalString($orderData, 'exec_id'),
            status: $this->reader->requireString($orderData, 'status'),
            accountId: $this->reader->optionalString($orderData, 'account_id') ?? $accountId,
            symbol: $this->reader->requireString($orderData, 'symbol'),
            quantity: $this->reader->requireDecimal($orderData, 'quantity'),
            side: $this->reader->requireString($orderData, 'side'),
            type: $this->reader->requireString($orderData, 'type'),
            timeInForce: $this->reader->requireString($orderData, 'time_in_force'),
            clientOrderId: $this->reader->optionalString($orderData, 'client_order_id'),
            comment: $this->reader->optionalString($orderData, 'comment'),
            limitPrice: $this->reader->optionalDecimal($orderData, 'limit_price'),
            stopPrice: $this->reader->optionalDecimal($orderData, 'stop_price'),
            transactAt: $this->reader->optionalDateTime($orderData, 'transact_at'),
            acceptAt: $this->reader->optionalDateTime($orderData, 'accept_at'),
            withdrawAt: $this->reader->optionalDateTime($orderData, 'withdraw_at'),
            initialQuantity: $this->reader->optionalDecimal($orderData, 'initial_quantity'),
            executedQuantity: $this->reader->optionalDecimal($orderData, 'executed_quantity'),
            remainingQuantity: $this->reader->optionalDecimal($orderData, 'remaining_quantity'),
        );
    }
}
