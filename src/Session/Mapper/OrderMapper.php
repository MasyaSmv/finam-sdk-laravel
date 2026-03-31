<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Collections\OrderCollection;
use MasyaSmv\FinamSdk\Dto\Order\OrderDto;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

/**
 * @phpstan-import-type ApiMap from ApiValueReader
 * @psalm-import-type ApiMap from ApiValueReader
 */
final class OrderMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    /**
     * @param ApiMap $data
     */
    public function mapCollection(array $data, string $accountId): OrderCollection
    {
        $orders = [];

        foreach ($this->reader->listOfArrays($data['orders'] ?? null, 'orders') as $orderData) {
            $orders[] = $this->map($orderData, $accountId);
        }

        /** @var list<OrderDto> $orders */
        return new OrderCollection($orders);
    }

    /**
     * @param ApiMap $data
     */
    public function map(array $data, string $accountId): OrderDto
    {
        /** @var ApiMap $orderData */
        $orderData = isset($data['order']) && is_array($data['order']) ? $data['order'] : $data;

        return new OrderDto(
            orderId: $this->reader->requireString($orderData, 'order_id'),
            execId: $this->reader->optionalString($orderData, 'exec_id'),
            status: $this->reader->requireString($orderData, 'status'),
            accountId: $this->reader->optionalString($orderData, 'account_id') ?? $accountId,
            symbol: $this->reader->requireString($orderData, 'symbol'),
            quantity: $this->reader->extractDecimalValue($this->reader->requireArray($orderData, 'quantity'), 'quantity'),
            side: $this->reader->requireString($orderData, 'side'),
            type: $this->reader->requireString($orderData, 'type'),
            timeInForce: $this->reader->requireString($orderData, 'time_in_force'),
            clientOrderId: $this->reader->optionalString($orderData, 'client_order_id'),
            comment: $this->reader->optionalString($orderData, 'comment'),
            limitPrice: $this->reader->extractOptionalDecimalValue($orderData['limit_price'] ?? null, 'limit_price'),
            stopPrice: $this->reader->extractOptionalDecimalValue($orderData['stop_price'] ?? null, 'stop_price'),
            transactAt: $this->reader->optionalDateTime($orderData, 'transact_at'),
            acceptAt: $this->reader->optionalDateTime($orderData, 'accept_at'),
            withdrawAt: $this->reader->optionalDateTime($orderData, 'withdraw_at'),
            initialQuantity: $this->reader->extractOptionalDecimalValue($orderData['initial_quantity'] ?? null, 'initial_quantity'),
            executedQuantity: $this->reader->extractOptionalDecimalValue($orderData['executed_quantity'] ?? null, 'executed_quantity'),
            remainingQuantity: $this->reader->extractOptionalDecimalValue($orderData['remaining_quantity'] ?? null, 'remaining_quantity'),
        );
    }
}
