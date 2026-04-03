<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Api;

use MasyaSmv\FinamSdk\Dto\Market\CandlesRequest;
use MasyaSmv\FinamSdk\Dto\Market\OrderbookRequest;
use MasyaSmv\FinamSdk\Dto\Market\QuotesRequest;
use MasyaSmv\FinamSdk\Dto\Market\TradesRequest;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

interface MarketApiInterface
{
    public function candles(CandlesRequest $request): ApiResponse;

    public function quotes(QuotesRequest $request): ApiResponse;

    public function orderbook(OrderbookRequest $request): ApiResponse;

    public function trades(TradesRequest $request): ApiResponse;
}
