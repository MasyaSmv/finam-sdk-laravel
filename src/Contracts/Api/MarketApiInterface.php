<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Api;

use MasyaSmv\FinamSdk\Dto\Market\CandlesRequest;
use MasyaSmv\FinamSdk\Dto\Market\OrderbookRequest;
use MasyaSmv\FinamSdk\Dto\Market\QuotesRequest;
use MasyaSmv\FinamSdk\Dto\Market\TradesRequest;

interface MarketApiInterface
{
    /**
     * @return array<string, mixed>
     */
    public function candles(CandlesRequest $request): array;

    /**
     * @return array<string, mixed>
     */
    public function quotes(QuotesRequest $request): array;

    /**
     * @return array<string, mixed>
     */
    public function orderbook(OrderbookRequest $request): array;

    /**
     * @return array<string, mixed>
     */
    public function trades(TradesRequest $request): array;
}
