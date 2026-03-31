<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use DateTimeImmutable;
use MasyaSmv\FinamSdk\Collections\OperationCollection;
use MasyaSmv\FinamSdk\Collections\OrderCollection;
use MasyaSmv\FinamSdk\Contracts\Api\AccountApiInterface;
use MasyaSmv\FinamSdk\Contracts\Api\ConnectApiInterface;
use MasyaSmv\FinamSdk\Contracts\Api\OrderApiInterface;
use MasyaSmv\FinamSdk\Dto\Account\GetAccountRequest;
use MasyaSmv\FinamSdk\Dto\Account\OperationDto;
use MasyaSmv\FinamSdk\Dto\Account\TradesRequest;
use MasyaSmv\FinamSdk\Dto\Account\TransactionsRequest;
use MasyaSmv\FinamSdk\Dto\Order\CancelOrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrderDto;
use MasyaSmv\FinamSdk\Dto\Order\OrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrdersRequest;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderInputDto;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\ReplaceOrderRequest;
use MasyaSmv\FinamSdk\Exceptions\AccountResolutionException;
use MasyaSmv\FinamSdk\Exceptions\ApiHttpException;
use MasyaSmv\FinamSdk\Session\FinamSession;

/**
 * @phpstan-type TestScalar null|bool|int|float|string
 * @phpstan-type TestNestedArray array<int|string, TestScalar|array<int|string, TestScalar|array<int|string, TestScalar|array<int|string, TestScalar>>>>
 * @phpstan-type TestResponse array<string, TestScalar|TestNestedArray>
 */
final class ConnectApiStub implements ConnectApiInterface
{
    /**
     * @param TestResponse $response
     */
    public function __construct(private array $response)
    {
    }

    public function tokenDetails(): array
    {
        return $this->response;
    }
}

/**
 * @phpstan-type TestScalar null|bool|int|float|string
 * @phpstan-type TestNestedArray array<int|string, TestScalar|array<int|string, TestScalar|array<int|string, TestScalar|array<int|string, TestScalar>>>>
 * @phpstan-type TestResponse array<string, TestScalar|TestNestedArray>
 */
final class AccountApiStub implements AccountApiInterface
{
    /**
     * @param TestResponse $response
     */
    public function __construct(private array $response)
    {
    }

    public function account(GetAccountRequest $request): array
    {
        return $this->response;
    }

    public function trades(TradesRequest $request): array
    {
        return $this->response;
    }

    public function transactions(TransactionsRequest $request): array
    {
        return $this->response;
    }
}

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

/**
 * @phpstan-type TestScalar null|bool|int|float|string
 * @phpstan-type TestNestedArray array<int|string, TestScalar|array<int|string, TestScalar|array<int|string, TestScalar|array<int|string, TestScalar>>>>
 * @phpstan-type TestResponse array<string, TestScalar|TestNestedArray>
 */
final class FinamSessionTest extends TestCase
{
    public function testSessionDetailsAreMappedToDto(): void
    {
        $session = new FinamSession(
            connectApi: new ConnectApiStub([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'created_at' => '2026-03-31T10:00:00+03:00',
                    'expires_at' => '2026-03-31T20:00:00+03:00',
                    'account_ids' => ['ACC-1'],
                    'readonly' => false,
                ],
                'error' => null,
                'meta' => [],
            ]),
            accountApi: new AccountApiStub([]),
            orderApi: new OrderApiStub([], [], []),
        );

        $details = $session->sessionDetails();

        $this->assertSame(['ACC-1'], $details->accountIds());
        $this->assertFalse($details->readonly());
        $this->assertSame('2026-03-31T10:00:00+03:00', $details->createdAt()->format(DATE_ATOM));
        $this->assertSame('2026-03-31T20:00:00+03:00', $details->expiresAt()->format(DATE_ATOM));
    }

    public function testGetOperationsByDateReturnsTypedCollection(): void
    {
        $session = new FinamSession(
            connectApi: new ConnectApiStub([]),
            accountApi: new AccountApiStub([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'transactions' => [
                        [
                            'id' => 'OP-1',
                            'category' => 'broker',
                            'transaction_category' => 'trade',
                            'transaction_name' => 'Buy',
                            'symbol' => 'SBER',
                            'timestamp' => '2026-03-31T10:00:00+03:00',
                            'change' => [
                                'currency_code' => 'RUB',
                                'units' => '1000',
                                'nanos' => 0,
                            ],
                            'change_qty' => '10',
                            'trade' => [
                                'trade_id' => 'TR-1',
                                'order_id' => 'OR-1',
                            ],
                        ],
                        [
                            'id' => 'OP-2',
                            'category' => 'broker',
                            'transaction_category' => 'fee',
                            'transaction_name' => 'Commission',
                            'symbol' => '',
                            'timestamp' => '2026-03-31T12:00:00+03:00',
                            'change' => [
                                'currency_code' => 'RUB',
                                'units' => '-50',
                                'nanos' => 0,
                            ],
                            'change_qty' => null,
                            'trade' => null,
                        ],
                    ],
                ],
                'error' => null,
                'meta' => [],
            ]),
            orderApi: new OrderApiStub([], [], []),
        );

        $operations = $session->getOperationsByDate(
            startDate: new DateTimeImmutable('2026-03-31T00:00:00+03:00'),
            endDate: new DateTimeImmutable('2026-03-31T23:59:59+03:00'),
            accountId: 'ACC-1',
        );

        /** @var OperationDto|null $firstOperation */
        $firstOperation = $operations->first();
        /** @var OperationDto|null $secondOperation */
        $secondOperation = $operations->get(1);

        $this->assertInstanceOf(OperationCollection::class, $operations);
        $this->assertCount(2, $operations);
        $this->assertSame('OP-1', $firstOperation?->id());
        $this->assertSame('OP-2', $secondOperation?->id());
        $this->assertSame('OP-1', $operations->findById('OP-1')?->id());
        $this->assertCount(
            1,
            $operations->between(
                new DateTimeImmutable('2026-03-31T09:00:00+03:00'),
                new DateTimeImmutable('2026-03-31T11:00:00+03:00'),
            ),
        );
    }

    public function testGetOperationsByDateResolvesSingleAccountFromSession(): void
    {
        $session = new FinamSession(
            connectApi: new ConnectApiStub([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'created_at' => '2026-03-31T10:00:00+03:00',
                    'expires_at' => '2026-03-31T20:00:00+03:00',
                    'account_ids' => ['ACC-1'],
                    'readonly' => false,
                ],
                'error' => null,
                'meta' => [],
            ]),
            accountApi: new AccountApiStub([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'transactions' => [],
                ],
                'error' => null,
                'meta' => [],
            ]),
            orderApi: new OrderApiStub([], [], []),
        );

        $operations = $session->getOperationsByDate(
            startDate: new DateTimeImmutable('2026-03-31T00:00:00+03:00'),
            endDate: new DateTimeImmutable('2026-03-31T23:59:59+03:00'),
        );

        $this->assertCount(0, $operations);
    }

    public function testGetOperationsByDateThrowsForAmbiguousAccount(): void
    {
        $session = new FinamSession(
            connectApi: new ConnectApiStub([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'created_at' => '2026-03-31T10:00:00+03:00',
                    'expires_at' => '2026-03-31T20:00:00+03:00',
                    'account_ids' => ['ACC-1', 'ACC-2'],
                    'readonly' => false,
                ],
                'error' => null,
                'meta' => [],
            ]),
            accountApi: new AccountApiStub([]),
            orderApi: new OrderApiStub([], [], []),
        );

        $this->expectException(AccountResolutionException::class);

        $session->getOperationsByDate(
            startDate: new DateTimeImmutable('2026-03-31T00:00:00+03:00'),
            endDate: new DateTimeImmutable('2026-03-31T23:59:59+03:00'),
        );
    }

    public function testApiErrorUsesFinamMessageAndCode(): void
    {
        $session = new FinamSession(
            connectApi: new ConnectApiStub([
                'ok' => false,
                'status' => 403,
                'data' => null,
                'error' => [
                    'message' => 'Token is invalid',
                    'code' => 'AUTH-403',
                ],
                'meta' => [
                    'headers' => [],
                ],
            ]),
            accountApi: new AccountApiStub([]),
            orderApi: new OrderApiStub([], [], []),
        );

        $this->expectException(ApiHttpException::class);
        $this->expectExceptionMessage('[AUTH-403] Token is invalid');

        $session->sessionDetails();
    }

    public function testGetOrdersReturnsTypedCollection(): void
    {
        $session = new FinamSession(
            connectApi: new ConnectApiStub([]),
            accountApi: new AccountApiStub([]),
            orderApi: new OrderApiStub(
                ordersResponse: [
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'orders' => [
                            [
                                'order_id' => 'ORD-1',
                                'exec_id' => 'EX-1',
                                'status' => 'ORDER_STATUS_ACTIVE',
                                'account_id' => 'ACC-1',
                                'symbol' => 'SBER@MISX',
                                'quantity' => ['value' => '10'],
                                'side' => 'SIDE_BUY',
                                'type' => 'ORDER_TYPE_LIMIT',
                                'time_in_force' => 'TIME_IN_FORCE_DAY',
                                'client_order_id' => 'CLIENT-1',
                                'comment' => 'First order',
                                'limit_price' => ['value' => '250.10'],
                                'initial_quantity' => ['value' => '10'],
                                'executed_quantity' => ['value' => '2'],
                                'remaining_quantity' => ['value' => '8'],
                                'transact_at' => '2026-03-31T10:00:00+03:00',
                            ],
                            [
                                'order_id' => 'ORD-2',
                                'exec_id' => null,
                                'status' => 'ORDER_STATUS_FILLED',
                                'account_id' => 'ACC-1',
                                'symbol' => 'GAZP@MISX',
                                'quantity' => ['value' => '5'],
                                'side' => 'SIDE_SELL',
                                'type' => 'ORDER_TYPE_MARKET',
                                'time_in_force' => 'TIME_IN_FORCE_DAY',
                                'client_order_id' => null,
                                'comment' => null,
                                'initial_quantity' => ['value' => '5'],
                                'executed_quantity' => ['value' => '5'],
                                'remaining_quantity' => ['value' => '0'],
                            ],
                        ],
                    ],
                    'error' => null,
                    'meta' => [],
                ],
                orderResponse: [],
                placeResponse: [],
            ),
        );

        $orders = $session->getOrders('ACC-1');

        /** @var OrderDto|null $firstOrder */
        $firstOrder = $orders->first();

        $this->assertInstanceOf(OrderCollection::class, $orders);
        $this->assertCount(2, $orders);
        $this->assertSame('ORD-1', $firstOrder?->orderId());
        $this->assertSame('ORD-2', $orders->findById('ORD-2')?->orderId());
    }

    public function testGetOrderReturnsDto(): void
    {
        $session = new FinamSession(
            connectApi: new ConnectApiStub([]),
            accountApi: new AccountApiStub([]),
            orderApi: new OrderApiStub(
                ordersResponse: [],
                orderResponse: [
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'order' => [
                            'order_id' => 'ORD-10',
                            'exec_id' => 'EX-10',
                            'status' => 'ORDER_STATUS_ACTIVE',
                            'account_id' => 'ACC-1',
                            'symbol' => 'AFLT@MISX',
                            'quantity' => ['value' => '3'],
                            'side' => 'SIDE_BUY',
                            'type' => 'ORDER_TYPE_LIMIT',
                            'time_in_force' => 'TIME_IN_FORCE_DAY',
                            'limit_price' => ['value' => '42.50'],
                        ],
                    ],
                    'error' => null,
                    'meta' => [],
                ],
                placeResponse: [],
            ),
        );

        $order = $session->getOrder('ORD-10', 'ACC-1');

        $this->assertSame('ORD-10', $order->orderId());
        $this->assertSame('ACC-1', $order->accountId());
        $this->assertSame('42.50', $order->limitPrice());
    }

    public function testPlaceOrderReturnsDto(): void
    {
        $session = new FinamSession(
            connectApi: new ConnectApiStub([]),
            accountApi: new AccountApiStub([]),
            orderApi: new OrderApiStub(
                ordersResponse: [],
                orderResponse: [],
                placeResponse: [
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'order' => [
                            'order_id' => 'ORD-77',
                            'exec_id' => null,
                            'status' => 'ORDER_STATUS_ACTIVE',
                            'account_id' => 'ACC-1',
                            'symbol' => 'LKOH@MISX',
                            'quantity' => ['value' => '1'],
                            'side' => 'SIDE_BUY',
                            'type' => 'ORDER_TYPE_LIMIT',
                            'time_in_force' => 'TIME_IN_FORCE_DAY',
                            'limit_price' => ['value' => '7000.00'],
                            'comment' => 'Test place order',
                        ],
                    ],
                    'error' => null,
                    'meta' => [],
                ],
            ),
        );

        $order = $session->placeOrder(
            new PlaceOrderInputDto(
                symbol: 'LKOH@MISX',
                quantity: '1',
                side: 'SIDE_BUY',
                type: 'ORDER_TYPE_LIMIT',
                timeInForce: 'TIME_IN_FORCE_DAY',
                limitPrice: '7000.00',
                comment: 'Test place order',
            ),
            'ACC-1',
        );

        $this->assertSame('ORD-77', $order->orderId());
        $this->assertSame('Test place order', $order->comment());
    }
}
