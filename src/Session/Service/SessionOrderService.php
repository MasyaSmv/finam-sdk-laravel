<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Service;

use MasyaSmv\FinamSdk\Collections\OrderCollection;
use MasyaSmv\FinamSdk\Contracts\Api\OrderApiInterface;
use MasyaSmv\FinamSdk\Contracts\Session\ApiResponseDecoderInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionAccountResolverInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionOrderServiceInterface;
use MasyaSmv\FinamSdk\Dto\Order\OrderDto;
use MasyaSmv\FinamSdk\Dto\Order\OrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrdersRequest;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderInputDto;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\PlaceSlTpOrderInputDto;
use MasyaSmv\FinamSdk\Dto\Order\PlaceSlTpOrderRequest;
use MasyaSmv\FinamSdk\Session\Mapper\OrderMapper;
final class SessionOrderService implements SessionOrderServiceInterface
{
    public function __construct(
        private OrderApiInterface $orderApi,
        private SessionAccountResolverInterface $accountResolver,
        private ApiResponseDecoderInterface $decoder,
        private OrderMapper $mapper,
    ) {
    }

    public function getOrders(?string $accountId = null): OrderCollection
    {
        $resolvedAccountId = $accountId ?? $this->accountResolver->resolveDefaultAccountId();
        $response = $this->orderApi->orders(new OrdersRequest($resolvedAccountId));
        $data = $this->decoder->extractData(
            $response,
            sprintf('accounts/%s/orders', $resolvedAccountId),
        );

        return $this->mapper->mapCollection($data, $resolvedAccountId);
    }

    public function getOrder(string $orderId, ?string $accountId = null): OrderDto
    {
        $resolvedAccountId = $accountId ?? $this->accountResolver->resolveDefaultAccountId();
        $response = $this->orderApi->order(new OrderRequest($resolvedAccountId, $orderId));
        $data = $this->decoder->extractData(
            $response,
            sprintf('accounts/%s/orders/%s', $resolvedAccountId, $orderId),
        );

        return $this->mapper->map($data, $resolvedAccountId);
    }

    public function placeOrder(PlaceOrderInputDto $order, ?string $accountId = null): OrderDto
    {
        $resolvedAccountId = $accountId ?? $this->accountResolver->resolveDefaultAccountId();
        $response = $this->orderApi->place(
            new PlaceOrderRequest(
                accountId: $resolvedAccountId,
                payload: $order,
            ),
        );
        $data = $this->decoder->extractData(
            $response,
            sprintf('accounts/%s/orders', $resolvedAccountId),
        );

        return $this->mapper->map($data, $resolvedAccountId);
    }

    public function placeSlTpOrder(PlaceSlTpOrderInputDto $order, ?string $accountId = null): OrderDto
    {
        $resolvedAccountId = $accountId ?? $this->accountResolver->resolveDefaultAccountId();
        $response = $this->orderApi->placeSlTp(
            new PlaceSlTpOrderRequest(
                accountId: $resolvedAccountId,
                payload: $order,
            ),
        );
        $data = $this->decoder->extractData(
            $response,
            sprintf('accounts/%s/orders/sltp', $resolvedAccountId),
        );

        return $this->mapper->map($data, $resolvedAccountId);
    }
}
