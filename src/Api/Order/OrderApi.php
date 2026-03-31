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
     *
     * @param OrdersRequest $request
     *
     * @return array<string, mixed>
     */
    public function orders(OrdersRequest $request): array
    {
        return $this->client->get("/accounts/{$request->accountId()}/orders", $request->toQuery());
    }

    /**
     * Детали заявки.
     *
     * @param OrderRequest $request
     *
     * @return array<string, mixed>
     */
    public function order(OrderRequest $request): array
    {
        return $this->client->get("/accounts/{$request->accountId()}/orders/{$request->orderId()}");
    }

    /**
     * Размещение заявки.
     *
     * @param PlaceOrderRequest $request
     *
     * @return array<string, mixed>
     */
    public function place(PlaceOrderRequest $request): array
    {
        return $this->client->post("/accounts/{$request->accountId()}/orders", $request->toPayload());
    }

    /**
     * Отмена заявки.
     *
     * @param CancelOrderRequest $request
     *
     * @return array<string, mixed>
     */
    public function cancel(CancelOrderRequest $request): array
    {
        return $this->client->post(
            "/accounts/{$request->accountId()}/orders/{$request->orderId()}/cancel",
            $request->toPayload(),
        );
    }

    /**
     * Замена (изменение) заявки.
     *
     * @param ReplaceOrderRequest $request
     *
     * @return array<string, mixed>
     */
    public function replace(ReplaceOrderRequest $request): array
    {
        return $this->client->post(
            "/accounts/{$request->accountId()}/orders/{$request->orderId()}/replace",
            $request->toPayload(),
        );
    }
}
