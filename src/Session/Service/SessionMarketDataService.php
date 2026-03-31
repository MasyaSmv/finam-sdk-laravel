<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Service;

use MasyaSmv\FinamSdk\Collections\CandleCollection;
use MasyaSmv\FinamSdk\Collections\QuoteCollection;
use MasyaSmv\FinamSdk\Collections\TradeCollection;
use MasyaSmv\FinamSdk\Contracts\Api\MarketApiInterface;
use MasyaSmv\FinamSdk\Contracts\Session\ApiResponseDecoderInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionMarketDataServiceInterface;
use MasyaSmv\FinamSdk\Dto\Market\CandlesQueryDto;
use MasyaSmv\FinamSdk\Dto\Market\CandlesRequest;
use MasyaSmv\FinamSdk\Dto\Market\OrderBookDto;
use MasyaSmv\FinamSdk\Dto\Market\OrderbookRequest;
use MasyaSmv\FinamSdk\Dto\Market\QuotesRequest;
use MasyaSmv\FinamSdk\Dto\Market\TradesRequest;
use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;
use MasyaSmv\FinamSdk\Session\Mapper\CandleMapper;
use MasyaSmv\FinamSdk\Session\Mapper\OrderBookMapper;
use MasyaSmv\FinamSdk\Session\Mapper\QuoteMapper;
use MasyaSmv\FinamSdk\Session\Mapper\TradeMapper;

final class SessionMarketDataService implements SessionMarketDataServiceInterface
{
    public function __construct(
        private MarketApiInterface $marketApi,
        private ApiResponseDecoderInterface $decoder,
        private QuoteMapper $quoteMapper,
        private CandleMapper $candleMapper,
        private OrderBookMapper $orderBookMapper,
        private TradeMapper $tradeMapper,
    ) {
    }

    public function getLatestQuotes(array $symbols): QuoteCollection
    {
        if ($symbols === []) {
            throw new InvalidRequestException('Symbols list must not be empty.');
        }

        $quotes = [];

        foreach ($symbols as $symbol) {
            if (!is_string($symbol) || $symbol === '') {
                throw new InvalidRequestException('Each symbol must be a non-empty string.');
            }

            $response = $this->marketApi->quotes(new QuotesRequest($symbol));
            $data = $this->decoder->extractData(
                $response,
                sprintf('instruments/%s/quotes/latest', $symbol),
            );
            $quotes[] = $this->quoteMapper->map($data);
        }

        /** @var list<\MasyaSmv\FinamSdk\Dto\Market\QuoteDto> $quotes */
        return $this->quoteMapper->mapCollection($quotes);
    }

    public function getCandles(CandlesQueryDto $query): CandleCollection
    {
        $response = $this->marketApi->candles(new CandlesRequest($query));
        $data = $this->decoder->extractData(
            $response,
            sprintf('instruments/%s/bars', $query->symbol()),
        );

        return $this->candleMapper->mapCollection($data);
    }

    public function getOrderBook(string $symbol): OrderBookDto
    {
        if ($symbol === '') {
            throw new InvalidRequestException('Symbol must not be empty.');
        }

        $response = $this->marketApi->orderbook(new OrderbookRequest($symbol));
        $data = $this->decoder->extractData(
            $response,
            sprintf('instruments/%s/orderbook', $symbol),
        );

        return $this->orderBookMapper->map($data);
    }

    public function getLatestTrades(string $symbol): TradeCollection
    {
        if ($symbol === '') {
            throw new InvalidRequestException('Symbol must not be empty.');
        }

        $response = $this->marketApi->trades(new TradesRequest($symbol));
        $data = $this->decoder->extractData(
            $response,
            sprintf('instruments/%s/trades/latest', $symbol),
        );

        return $this->tradeMapper->mapCollection($data);
    }
}
