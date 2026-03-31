<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use DateTimeImmutable;
use MasyaSmv\FinamSdk\Collections\CandleCollection;
use MasyaSmv\FinamSdk\Collections\QuoteCollection;
use MasyaSmv\FinamSdk\Dto\Market\CandleDto;
use MasyaSmv\FinamSdk\Dto\Market\CandlesQueryDto;
use MasyaSmv\FinamSdk\Dto\Market\QuoteDto;
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
}
