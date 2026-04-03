<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Api\Market;

use MasyaSmv\FinamSdk\Contracts\Api\MarketApiInterface;
use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Dto\Market\CandlesRequest;
use MasyaSmv\FinamSdk\Dto\Market\OrderbookRequest;
use MasyaSmv\FinamSdk\Dto\Market\QuotesRequest;
use MasyaSmv\FinamSdk\Dto\Market\TradesRequest;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

/**
 * MarketApi — котировки/свечи/стакан/лента (market data).
 */
final class MarketApi implements MarketApiInterface
{
    public function __construct(private FinamClient $client)
    {
    }

    /**
     * Свечи (candles).
     */
    public function candles(CandlesRequest $request): ApiResponse
    {
        return $this->client->get("/instruments/{$request->symbol()}/bars/", $request->toQuery());
    }

    /**
     * Котировки (quotes).
     */
    public function quotes(QuotesRequest $request): ApiResponse
    {
        return $this->client->get("/instruments/{$request->symbol()}/quotes/latest", $request->toQuery());
    }

    /**
     * Стакан (orderbook/level2).
     */
    public function orderbook(OrderbookRequest $request): ApiResponse
    {
        return $this->client->get("/instruments/{$request->symbol()}/orderbook", $request->toQuery());
    }

    /**
     * Лента сделок (trades).
     */
    public function trades(TradesRequest $request): ApiResponse
    {
        return $this->client->get("/instruments/{$request->symbol()}/trades/latest", $request->toQuery());
    }
}
