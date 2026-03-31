<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Api;

use MasyaSmv\FinamSdk\Dto\Order\CancelOrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrdersRequest;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\PlaceSlTpOrderRequest;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

interface OrderApiInterface
{
    public function orders(OrdersRequest $request): ApiResponse;

    public function order(OrderRequest $request): ApiResponse;

    public function place(PlaceOrderRequest $request): ApiResponse;

    public function placeSlTp(PlaceSlTpOrderRequest $request): ApiResponse;

    public function cancel(CancelOrderRequest $request): ApiResponse;
}
