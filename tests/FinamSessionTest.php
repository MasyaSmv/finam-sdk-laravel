<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use DateTimeImmutable;
use MasyaSmv\FinamSdk\Collections\OperationCollection;
use MasyaSmv\FinamSdk\Contracts\Api\AccountApiInterface;
use MasyaSmv\FinamSdk\Contracts\Api\ConnectApiInterface;
use MasyaSmv\FinamSdk\Dto\Account\GetAccountRequest;
use MasyaSmv\FinamSdk\Dto\Account\OperationDto;
use MasyaSmv\FinamSdk\Dto\Account\TradesRequest;
use MasyaSmv\FinamSdk\Dto\Account\TransactionsRequest;
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
        );

        $this->expectException(ApiHttpException::class);
        $this->expectExceptionMessage('[AUTH-403] Token is invalid');

        $session->sessionDetails();
    }
}
