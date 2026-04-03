<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use DateTimeImmutable;
use MasyaSmv\FinamSdk\Collections\OperationCollection;
use MasyaSmv\FinamSdk\Dto\Account\OperationDto;
use MasyaSmv\FinamSdk\Exceptions\AccountResolutionException;
use MasyaSmv\FinamSdk\Session\FinamSession;
use MasyaSmv\FinamSdk\Tests\Support\AccountApiStub;
use MasyaSmv\FinamSdk\Tests\Support\ConnectApiStub;
use MasyaSmv\FinamSdk\Tests\Support\InstrumentApiStub;
use MasyaSmv\FinamSdk\Tests\Support\MarketApiStub;
use MasyaSmv\FinamSdk\Tests\Support\OrderApiStub;
use MasyaSmv\FinamSdk\Tests\Support\TestApiResponseFactory;

final class OperationSessionTest extends TestCase
{
    public function testGetOperationsByDateReturnsTypedCollection(): void
    {
        $session = FinamSession::fromApis(
            connectApi: new ConnectApiStub(TestApiResponseFactory::fromArray([])),
            accountApi: new AccountApiStub(TestApiResponseFactory::fromArray([
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
            ])),
            orderApi: new OrderApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
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
        $session = FinamSession::fromApis(
            connectApi: new ConnectApiStub(TestApiResponseFactory::fromArray([
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
            ])),
            accountApi: new AccountApiStub(TestApiResponseFactory::fromArray([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'transactions' => [],
                ],
                'error' => null,
                'meta' => [],
            ])),
            orderApi: new OrderApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
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

        $operations = $session->getOperationsByDate(
            startDate: new DateTimeImmutable('2026-03-31T00:00:00+03:00'),
            endDate: new DateTimeImmutable('2026-03-31T23:59:59+03:00'),
        );

        $this->assertCount(0, $operations);
    }

    public function testGetOperationsByDateThrowsForAmbiguousAccount(): void
    {
        $session = FinamSession::fromApis(
            connectApi: new ConnectApiStub(TestApiResponseFactory::fromArray([
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
            ])),
            accountApi: new AccountApiStub(TestApiResponseFactory::fromArray([])),
            orderApi: new OrderApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
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

        $this->expectException(AccountResolutionException::class);

        $session->getOperationsByDate(
            startDate: new DateTimeImmutable('2026-03-31T00:00:00+03:00'),
            endDate: new DateTimeImmutable('2026-03-31T23:59:59+03:00'),
        );
    }
}
