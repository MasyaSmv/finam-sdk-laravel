<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Api\Market;

use MasyaSmv\FinamSdk\Contracts\Api\MarketApiInterface;
use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Dto\Market\CandlesRequest;
use MasyaSmv\FinamSdk\Dto\Market\OrderbookRequest;
use MasyaSmv\FinamSdk\Dto\Market\QuotesRequest;
use MasyaSmv\FinamSdk\Dto\Market\TradesRequest;

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
     *
     * @param CandlesRequest $request
     *
     * @return array<string, mixed>
     */
    public function candles(CandlesRequest $request): array
    {
        return $this->client->get('/market/candles', $request->toQuery());
    }

    /**
     * Котировки (quotes).
     *
     * @param QuotesRequest $request
     *
     * @return array<string, mixed>
     */
    public function quotes(QuotesRequest $request): array
    {
        return $this->client->get('/market/quotes', $request->toQuery());
    }

    /**
     * Стакан (orderbook/level2).
     *
     * @param OrderbookRequest $request
     *
     * @return array<string, mixed>
     */
    public function orderbook(OrderbookRequest $request): array
    {
        return $this->client->get('/market/orderbook', $request->toQuery());
    }

    /**
     * Лента сделок (trades).
     *
     * @param TradesRequest $request
     *
     * @return array<string, mixed>
     */
    public function trades(TradesRequest $request): array
    {
        return $this->client->get('/market/trades', $request->toQuery());
    }
}
