<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests\Support;

use MasyaSmv\FinamSdk\Contracts\Api\MarketApiInterface;
use MasyaSmv\FinamSdk\Dto\Market\CandlesRequest;
use MasyaSmv\FinamSdk\Dto\Market\OrderbookRequest;
use MasyaSmv\FinamSdk\Dto\Market\QuotesRequest;
use MasyaSmv\FinamSdk\Dto\Market\TradesRequest as MarketTradesRequest;

/**
 * @phpstan-type TestScalar null|bool|int|float|string
 * @phpstan-type TestNestedArray array<int|string, TestScalar|array<int|string, TestScalar|array<int|string, TestScalar|array<int|string, TestScalar>>>>
 * @phpstan-type TestResponse array<string, TestScalar|TestNestedArray>
 */
final class MarketApiStub implements MarketApiInterface
{
    /**
     * @param TestResponse $quotesResponse
     * @param TestResponse $candlesResponse
     */
    public function __construct(
        private array $quotesResponse,
        private array $candlesResponse,
    ) {
    }

    public function candles(CandlesRequest $request): array
    {
        return $this->candlesResponse;
    }

    public function quotes(QuotesRequest $request): array
    {
        return $this->quotesResponse;
    }

    public function orderbook(OrderbookRequest $request): array
    {
        return [];
    }

    public function trades(MarketTradesRequest $request): array
    {
        return [];
    }
}
