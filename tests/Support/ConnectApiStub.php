<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests\Support;

use MasyaSmv\FinamSdk\Contracts\Api\ConnectApiInterface;

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
