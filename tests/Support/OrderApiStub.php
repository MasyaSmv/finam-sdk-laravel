<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests\Support;

use MasyaSmv\FinamSdk\Contracts\Api\OrderApiInterface;
use MasyaSmv\FinamSdk\Dto\Order\CancelOrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrdersRequest;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderRequest;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

final class OrderApiStub implements OrderApiInterface
{
    public function __construct(
        private ApiResponse $ordersResponse,
        private ApiResponse $orderResponse,
        private ApiResponse $placeResponse,
    ) {
    }

    public function orders(OrdersRequest $request): ApiResponse
    {
        return $this->ordersResponse;
    }

    public function order(OrderRequest $request): ApiResponse
    {
        return $this->orderResponse;
    }

    public function place(PlaceOrderRequest $request): ApiResponse
    {
        return $this->placeResponse;
    }

    public function cancel(CancelOrderRequest $request): ApiResponse
    {
        return TestApiResponseFactory::fromArray([]);
    }
}
