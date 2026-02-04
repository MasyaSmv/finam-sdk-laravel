<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Api;

use MasyaSmv\FinamSdk\Dto\Order\CancelOrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrdersRequest;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\ReplaceOrderRequest;

interface OrderApiInterface
{
    /**
     * @return array<string, mixed>
     */
    public function orders(OrdersRequest $request): array;

    /**
     * @return array<string, mixed>
     */
    public function order(OrderRequest $request): array;

    /**
     * @return array<string, mixed>
     */
    public function place(PlaceOrderRequest $request): array;

    /**
     * @return array<string, mixed>
     */
    public function cancel(CancelOrderRequest $request): array;

    /**
     * @return array<string, mixed>
     */
    public function replace(ReplaceOrderRequest $request): array;
}
