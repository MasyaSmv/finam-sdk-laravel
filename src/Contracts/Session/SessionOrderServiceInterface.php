<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Session;

use MasyaSmv\FinamSdk\Collections\OrderCollection;
use MasyaSmv\FinamSdk\Dto\Order\OrderDto;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderInputDto;
use MasyaSmv\FinamSdk\Dto\Order\PlaceSlTpOrderInputDto;

interface SessionOrderServiceInterface
{
    public function getOrders(?string $accountId = null): OrderCollection;

    public function getOrder(string $orderId, ?string $accountId = null): OrderDto;

    public function placeOrder(PlaceOrderInputDto $order, ?string $accountId = null): OrderDto;

    public function placeSlTpOrder(PlaceSlTpOrderInputDto $order, ?string $accountId = null): OrderDto;
}
