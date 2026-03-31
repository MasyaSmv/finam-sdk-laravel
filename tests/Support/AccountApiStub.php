<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests\Support;

use MasyaSmv\FinamSdk\Contracts\Api\AccountApiInterface;
use MasyaSmv\FinamSdk\Dto\Account\GetAccountRequest;
use MasyaSmv\FinamSdk\Dto\Account\TradesRequest;
use MasyaSmv\FinamSdk\Dto\Account\TransactionsRequest;

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
