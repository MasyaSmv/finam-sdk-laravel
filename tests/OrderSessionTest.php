<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use MasyaSmv\FinamSdk\Collections\OrderCollection;
use MasyaSmv\FinamSdk\Dto\Order\OrderDto;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderInputDto;
use MasyaSmv\FinamSdk\Session\FinamSession;
use MasyaSmv\FinamSdk\Tests\Support\AccountApiStub;
use MasyaSmv\FinamSdk\Tests\Support\ConnectApiStub;
use MasyaSmv\FinamSdk\Tests\Support\InstrumentApiStub;
use MasyaSmv\FinamSdk\Tests\Support\MarketApiStub;
use MasyaSmv\FinamSdk\Tests\Support\OrderApiStub;
use MasyaSmv\FinamSdk\Tests\Support\TestApiResponseFactory;

final class OrderSessionTest extends TestCase
{
    public function testGetOrdersReturnsTypedCollection(): void
    {
        $session = FinamSession::fromApis(
            connectApi: new ConnectApiStub(TestApiResponseFactory::fromArray([])),
            accountApi: new AccountApiStub(TestApiResponseFactory::fromArray([])),
            orderApi: new OrderApiStub(
                ordersResponse: TestApiResponseFactory::fromArray([
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
                ]),
                orderResponse: TestApiResponseFactory::fromArray([]),
                placeResponse: TestApiResponseFactory::fromArray([]),
            ),
            instrumentApi: new InstrumentApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
            marketApi: new MarketApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
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
        $session = FinamSession::fromApis(
            connectApi: new ConnectApiStub(TestApiResponseFactory::fromArray([])),
            accountApi: new AccountApiStub(TestApiResponseFactory::fromArray([])),
            orderApi: new OrderApiStub(
                ordersResponse: TestApiResponseFactory::fromArray([]),
                orderResponse: TestApiResponseFactory::fromArray([
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
                ]),
                placeResponse: TestApiResponseFactory::fromArray([]),
            ),
            instrumentApi: new InstrumentApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
            marketApi: new MarketApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
        );

        $order = $session->getOrder('ORD-10', 'ACC-1');

        $this->assertSame('ORD-10', $order->orderId());
        $this->assertSame('ACC-1', $order->accountId());
        $this->assertSame('42.50', $order->limitPrice());
    }

    public function testPlaceOrderReturnsDto(): void
    {
        $session = FinamSession::fromApis(
            connectApi: new ConnectApiStub(TestApiResponseFactory::fromArray([])),
            accountApi: new AccountApiStub(TestApiResponseFactory::fromArray([])),
            orderApi: new OrderApiStub(
                ordersResponse: TestApiResponseFactory::fromArray([]),
                orderResponse: TestApiResponseFactory::fromArray([]),
                placeResponse: TestApiResponseFactory::fromArray([
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
                ]),
            ),
            instrumentApi: new InstrumentApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
            marketApi: new MarketApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
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
