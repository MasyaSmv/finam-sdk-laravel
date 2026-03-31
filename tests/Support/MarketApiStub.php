<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests\Support;

use MasyaSmv\FinamSdk\Contracts\Api\MarketApiInterface;
use MasyaSmv\FinamSdk\Dto\Market\CandlesRequest;
use MasyaSmv\FinamSdk\Dto\Market\OrderbookRequest;
use MasyaSmv\FinamSdk\Dto\Market\QuotesRequest;
use MasyaSmv\FinamSdk\Dto\Market\TradesRequest as MarketTradesRequest;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

final class MarketApiStub implements MarketApiInterface
{
    public function __construct(
        private ApiResponse $quotesResponse,
        private ApiResponse $candlesResponse,
    ) {
    }

    public function candles(CandlesRequest $request): ApiResponse
    {
        return $this->candlesResponse;
    }

    public function quotes(QuotesRequest $request): ApiResponse
    {
        return $this->quotesResponse;
    }

    public function orderbook(OrderbookRequest $request): ApiResponse
    {
        return TestApiResponseFactory::fromArray([]);
    }

    public function trades(MarketTradesRequest $request): ApiResponse
    {
        return TestApiResponseFactory::fromArray([]);
    }
}
