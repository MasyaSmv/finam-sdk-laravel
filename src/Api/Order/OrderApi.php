<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Api\Order;

use MasyaSmv\FinamSdk\Contracts\Api\OrderApiInterface;
use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Dto\Order\CancelOrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrdersRequest;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\ReplaceOrderRequest;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

/**
 * OrderApi — торговые заявки и операции с ними.
 */
final class OrderApi implements OrderApiInterface
{
    public function __construct(private FinamClient $client)
    {
    }

    /**
     * Список заявок.
     */
    public function orders(OrdersRequest $request): ApiResponse
    {
        return $this->client->get("/accounts/{$request->accountId()}/orders", $request->toQuery());
    }

    /**
     * Детали заявки.
     */
    public function order(OrderRequest $request): ApiResponse
    {
        return $this->client->get("/accounts/{$request->accountId()}/orders/{$request->orderId()}");
    }

    /**
     * Размещение заявки.
     */
    public function place(PlaceOrderRequest $request): ApiResponse
    {
        return $this->client->post("/accounts/{$request->accountId()}/orders", $request->toPayload());
    }

    /**
     * Отмена заявки.
     */
    public function cancel(CancelOrderRequest $request): ApiResponse
    {
        return $this->client->post(
            "/accounts/{$request->accountId()}/orders/{$request->orderId()}/cancel",
            $request->toPayload(),
        );
    }

    /**
     * Замена (изменение) заявки.
     */
    public function replace(ReplaceOrderRequest $request): ApiResponse
    {
        return $this->client->post(
            "/accounts/{$request->accountId()}/orders/{$request->orderId()}/replace",
            $request->toPayload(),
        );
    }
}
