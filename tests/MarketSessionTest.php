<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use DateTimeImmutable;
use MasyaSmv\FinamSdk\Collections\CandleCollection;
use MasyaSmv\FinamSdk\Collections\QuoteCollection;
use MasyaSmv\FinamSdk\Collections\TradeCollection;
use MasyaSmv\FinamSdk\Collections\OrderBookRowCollection;
use MasyaSmv\FinamSdk\Dto\Market\CandleDto;
use MasyaSmv\FinamSdk\Dto\Market\CandlesQueryDto;
use MasyaSmv\FinamSdk\Dto\Market\OrderBookDto;
use MasyaSmv\FinamSdk\Dto\Market\OrderBookRowDto;
use MasyaSmv\FinamSdk\Dto\Market\QuoteDto;
use MasyaSmv\FinamSdk\Dto\Market\TradeDto;
use MasyaSmv\FinamSdk\Session\FinamSession;
use MasyaSmv\FinamSdk\Tests\Support\AccountApiStub;
use MasyaSmv\FinamSdk\Tests\Support\ConnectApiStub;
use MasyaSmv\FinamSdk\Tests\Support\InstrumentApiStub;
use MasyaSmv\FinamSdk\Tests\Support\MarketApiStub;
use MasyaSmv\FinamSdk\Tests\Support\OrderApiStub;
use MasyaSmv\FinamSdk\Tests\Support\TestApiResponseFactory;

final class MarketSessionTest extends TestCase
{
    public function testGetLatestQuotesReturnsTypedCollection(): void
    {
        $session = FinamSession::fromApis(
            connectApi: new ConnectApiStub(TestApiResponseFactory::fromArray([])),
            accountApi: new AccountApiStub(TestApiResponseFactory::fromArray([])),
            orderApi: new OrderApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
            instrumentApi: new InstrumentApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
            marketApi: new MarketApiStub(
                quotesResponse: TestApiResponseFactory::fromArray([
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'symbol' => 'SBER@MISX',
                        'quote' => [
                            'symbol' => 'SBER@MISX',
                            'last' => ['value' => '250.10'],
                            'change' => ['value' => '1.20'],
                            'timestamp' => '2026-03-31T12:00:00+03:00',
                        ],
                    ],
                    'error' => null,
                    'meta' => [],
                ]),
                candlesResponse: TestApiResponseFactory::fromArray([]),
                orderbookResponse: TestApiResponseFactory::fromArray([]),
                tradesResponse: TestApiResponseFactory::fromArray([]),
            ),
        );

        $quotes = $session->getLatestQuotes(['SBER@MISX']);

        /** @var QuoteDto|null $firstQuote */
        $firstQuote = $quotes->first();

        $this->assertInstanceOf(QuoteCollection::class, $quotes);
        $this->assertSame('250.10', $firstQuote?->price());
        $this->assertSame('SBER@MISX', $quotes->findBySymbol('SBER@MISX')?->symbol());
    }

    public function testGetCandlesReturnsTypedCollection(): void
    {
        $session = FinamSession::fromApis(
            connectApi: new ConnectApiStub(TestApiResponseFactory::fromArray([])),
            accountApi: new AccountApiStub(TestApiResponseFactory::fromArray([])),
            orderApi: new OrderApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
            instrumentApi: new InstrumentApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
            marketApi: new MarketApiStub(
                quotesResponse: TestApiResponseFactory::fromArray([]),
                candlesResponse: TestApiResponseFactory::fromArray([
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'symbol' => 'SBER@MISX',
                        'bars' => [
                            [
                                'timestamp' => '2026-03-31T10:00:00+03:00',
                                'open' => ['value' => '100.0'],
                                'high' => ['value' => '105.0'],
                                'low' => ['value' => '99.0'],
                                'close' => ['value' => '104.5'],
                                'volume' => ['value' => '10000'],
                            ],
                        ],
                    ],
                    'error' => null,
                    'meta' => [],
                ]),
                orderbookResponse: TestApiResponseFactory::fromArray([]),
                tradesResponse: TestApiResponseFactory::fromArray([]),
            ),
        );

        $candles = $session->getCandles(
            new CandlesQueryDto(
                symbol: 'SBER@MISX',
                timeframe: 'TIMEFRAME_M1',
                startDate: new DateTimeImmutable('2026-03-31T10:00:00+03:00'),
                endDate: new DateTimeImmutable('2026-03-31T11:00:00+03:00'),
            ),
        );

        /** @var CandleDto|null $firstCandle */
        $firstCandle = $candles->first();

        $this->assertInstanceOf(CandleCollection::class, $candles);
        $this->assertSame('104.5', $firstCandle?->close());
    }

    public function testGetOrderBookReturnsTypedDto(): void
    {
        $session = FinamSession::fromApis(
            connectApi: new ConnectApiStub(TestApiResponseFactory::fromArray([])),
            accountApi: new AccountApiStub(TestApiResponseFactory::fromArray([])),
            orderApi: new OrderApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
            instrumentApi: new InstrumentApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
            marketApi: new MarketApiStub(
                quotesResponse: TestApiResponseFactory::fromArray([]),
                candlesResponse: TestApiResponseFactory::fromArray([]),
                orderbookResponse: TestApiResponseFactory::fromArray([
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'symbol' => 'SBER@MISX',
                        'orderbook' => [
                            'rows' => [
                                [
                                    'price' => ['value' => '250.10'],
                                    'buy_size' => ['value' => '100'],
                                    'sell_size' => ['value' => '0'],
                                    'action' => 'ACTION_ADD',
                                ],
                                [
                                    'price' => ['value' => '250.20'],
                                    'buy_size' => ['value' => '0'],
                                    'sell_size' => ['value' => '120'],
                                    'action' => 'ACTION_ADD',
                                ],
                            ],
                        ],
                    ],
                    'error' => null,
                    'meta' => [],
                ]),
                tradesResponse: TestApiResponseFactory::fromArray([]),
            ),
        );

        $orderBook = $session->getOrderBook('SBER@MISX');
        /** @var OrderBookRowDto|null $bestBid */
        $bestBid = $orderBook->buyRows()->first();
        /** @var OrderBookRowDto|null $bestAsk */
        $bestAsk = $orderBook->sellRows()->first();

        $this->assertInstanceOf(OrderBookDto::class, $orderBook);
        $this->assertInstanceOf(OrderBookRowCollection::class, $orderBook->rows());
        $this->assertInstanceOf(OrderBookRowCollection::class, $orderBook->buyRows());
        $this->assertInstanceOf(OrderBookRowCollection::class, $orderBook->sellRows());
        $this->assertNotNull($bestBid);
        $this->assertNotNull($bestAsk);
        $this->assertSame('SBER@MISX', $orderBook->symbol());
        $this->assertSame('250.10', $bestBid->price());
        $this->assertSame('100', $bestBid->buySize());
        $this->assertSame('250.20', $bestAsk->price());
        $this->assertSame('120', $bestAsk->sellSize());
    }

    public function testGetOrderBookDefaultsMissingSideSizeToZero(): void
    {
        $session = FinamSession::fromApis(
            connectApi: new ConnectApiStub(TestApiResponseFactory::fromArray([])),
            accountApi: new AccountApiStub(TestApiResponseFactory::fromArray([])),
            orderApi: new OrderApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
            instrumentApi: new InstrumentApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
            marketApi: new MarketApiStub(
                quotesResponse: TestApiResponseFactory::fromArray([]),
                candlesResponse: TestApiResponseFactory::fromArray([]),
                orderbookResponse: TestApiResponseFactory::fromArray([
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'symbol' => 'SBER@MISX',
                        'orderbook' => [
                            'rows' => [
                                [
                                    'price' => ['value' => '250.10'],
                                    'sell_size' => ['value' => '12.0'],
                                    'action' => 'ACTION_ADD',
                                ],
                            ],
                        ],
                    ],
                    'error' => null,
                    'meta' => [],
                ]),
                tradesResponse: TestApiResponseFactory::fromArray([]),
            ),
        );

        /** @var OrderBookRowDto|null $firstRow */
        $firstRow = $session->getOrderBook('SBER@MISX')->rows()->first();

        $this->assertNotNull($firstRow);
        $this->assertSame('12.0', $firstRow->sellSize());
        $this->assertSame('0', $firstRow->buySize());
    }

    public function testGetLatestTradesReturnsTypedCollection(): void
    {
        $session = FinamSession::fromApis(
            connectApi: new ConnectApiStub(TestApiResponseFactory::fromArray([])),
            accountApi: new AccountApiStub(TestApiResponseFactory::fromArray([])),
            orderApi: new OrderApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
            instrumentApi: new InstrumentApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
            marketApi: new MarketApiStub(
                quotesResponse: TestApiResponseFactory::fromArray([]),
                candlesResponse: TestApiResponseFactory::fromArray([]),
                orderbookResponse: TestApiResponseFactory::fromArray([]),
                tradesResponse: TestApiResponseFactory::fromArray([
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'symbol' => 'SBER@MISX',
                        'trades' => [
                            [
                                'price' => ['value' => '250.10'],
                                'size' => ['value' => '5'],
                                'timestamp' => '2026-04-01T12:00:00+03:00',
                                'side' => 'SIDE_BUY',
                            ],
                        ],
                    ],
                    'error' => null,
                    'meta' => [],
                ]),
            ),
        );

        $trades = $session->getLatestTrades('SBER@MISX');

        /** @var TradeDto|null $firstTrade */
        $firstTrade = $trades->first();

        $this->assertInstanceOf(TradeCollection::class, $trades);
        $this->assertNotNull($firstTrade);
        $this->assertSame('SBER@MISX', $firstTrade->symbol());
        $this->assertSame('250.10', $firstTrade->price());
        $this->assertSame('5', $firstTrade->size());
        $this->assertSame('SIDE_BUY', $firstTrade->side());
    }
}
