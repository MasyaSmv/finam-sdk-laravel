<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests\Support;

use MasyaSmv\FinamSdk\Contracts\Api\OrderApiInterface;
use MasyaSmv\FinamSdk\Dto\Order\CancelOrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrdersRequest;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\ReplaceOrderRequest;

/**
 * @phpstan-type TestScalar null|bool|int|float|string
 * @phpstan-type TestNestedArray array<int|string, TestScalar|array<int|string, TestScalar|array<int|string, TestScalar|array<int|string, TestScalar>>>>
 * @phpstan-type TestResponse array<string, TestScalar|TestNestedArray>
 */
final class OrderApiStub implements OrderApiInterface
{
    /**
     * @param TestResponse $ordersResponse
     * @param TestResponse $orderResponse
     * @param TestResponse $placeResponse
     */
    public function __construct(
        private array $ordersResponse,
        private array $orderResponse,
        private array $placeResponse,
    ) {
    }

    public function orders(OrdersRequest $request): array
    {
        return $this->ordersResponse;
    }

    public function order(OrderRequest $request): array
    {
        return $this->orderResponse;
    }

    public function place(PlaceOrderRequest $request): array
    {
        return $this->placeResponse;
    }

    public function cancel(CancelOrderRequest $request): array
    {
        return [];
    }

    public function replace(ReplaceOrderRequest $request): array
    {
        return [];
    }
}
