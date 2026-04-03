<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use DateTimeImmutable;
use MasyaSmv\FinamSdk\Collections\ExchangeCollection;
use MasyaSmv\FinamSdk\Collections\InstrumentCollection;
use MasyaSmv\FinamSdk\Collections\ScheduleSessionCollection;
use MasyaSmv\FinamSdk\Dto\Instrument\AllAssetsPageDto;
use MasyaSmv\FinamSdk\Dto\Instrument\ClockDto;
use MasyaSmv\FinamSdk\Dto\Instrument\ExchangeDto;
use MasyaSmv\FinamSdk\Dto\Instrument\InstrumentDto;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleDto;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleSessionDto;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Exceptions\ResponseMappingException;
use MasyaSmv\FinamSdk\Session\FinamSession;
use MasyaSmv\FinamSdk\Session\Mapper\InstrumentMapper;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;
use MasyaSmv\FinamSdk\Tests\Support\AccountApiStub;
use MasyaSmv\FinamSdk\Tests\Support\ConnectApiStub;
use MasyaSmv\FinamSdk\Tests\Support\InstrumentApiStub;
use MasyaSmv\FinamSdk\Tests\Support\MarketApiStub;
use MasyaSmv\FinamSdk\Tests\Support\OrderApiStub;
use MasyaSmv\FinamSdk\Tests\Support\TestApiResponseFactory;

final class InstrumentSessionTest extends TestCase
{
    public function testInstrumentMapperMapsCollectionAndTickerOnlySymbolFallback(): void
    {
        $mapper = new InstrumentMapper(new ApiValueReader());

        $collection = $mapper->mapCollection(new ApiPayload([
            'assets' => [
                [
                    'ticker' => 'IMOEX',
                    'type' => 'ASSET_TYPE_INDEX',
                    'name' => 'MOEX Index',
                ],
            ],
        ]));

        $instrument = $mapper->map(new ApiPayload([
            'ticker' => 'IMOEX',
            'type' => 'ASSET_TYPE_INDEX',
            'name' => 'MOEX Index',
        ]));

        $this->assertInstanceOf(InstrumentCollection::class, $collection);
        $this->assertSame('IMOEX', $collection->first()?->symbol());
        $this->assertSame('IMOEX', $instrument->symbol());

        $this->expectException(ResponseMappingException::class);
        $mapper->map(new ApiPayload([
            'name' => 'Instrument without symbol and ticker',
        ]));
    }

    public function testGetInstrumentsReturnsTypedCollection(): void
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
                assetsResponse: TestApiResponseFactory::fromArray([]),
                assetResponse: TestApiResponseFactory::fromArray([]),
                allAssetsResponse: TestApiResponseFactory::fromArray([
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'assets' => [
                            [
                                'id' => 'asset-1',
                                'symbol' => 'SBER@MISX',
                                'ticker' => 'SBER',
                                'mic' => 'MISX',
                                'type' => 'ASSET_TYPE_STOCK',
                                'name' => 'Sberbank',
                                'quote_currency' => 'RUB',
                                'lot_size' => ['value' => '10'],
                                'isin' => 'RU0009029540',
                            ],
                        ],
                    ],
                    'error' => null,
                    'meta' => [],
                ]),
            ),
            marketApi: new MarketApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
        );

        $instruments = $session->getInstruments();

        /** @var InstrumentDto|null $firstInstrument */
        $firstInstrument = $instruments->first();

        $this->assertInstanceOf(InstrumentCollection::class, $instruments);
        $this->assertNotNull($firstInstrument);
        $this->assertSame('SBER@MISX', $firstInstrument->symbol());
        $this->assertSame('asset-1', $firstInstrument->id());
        $this->assertSame('SBER', $firstInstrument->ticker());
        $this->assertSame('MISX', $firstInstrument->mic());
        $this->assertSame('ASSET_TYPE_STOCK', $firstInstrument->type());
        $this->assertSame('Sberbank', $firstInstrument->name());
        $this->assertSame('RUB', $firstInstrument->quoteCurrency());
        $this->assertSame('SBER@MISX', $instruments->findBySymbol('SBER@MISX')?->symbol());
    }

    public function testGetInstrumentReturnsDto(): void
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
                assetsResponse: TestApiResponseFactory::fromArray([]),
                assetResponse: TestApiResponseFactory::fromArray([
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'asset' => [
                            'id' => 'asset-2',
                            'symbol' => 'GAZP@MISX',
                            'ticker' => 'GAZP',
                            'mic' => 'MISX',
                            'type' => 'ASSET_TYPE_STOCK',
                            'name' => 'Gazprom PJSC',
                            'board' => 'TQBR',
                            'decimals' => 2,
                            'min_step' => ['value' => '0.01'],
                            'quote_currency' => 'RUB',
                            'lot_size' => ['value' => '10'],
                        ],
                    ],
                    'error' => null,
                    'meta' => [],
                ]),
                allAssetsResponse: TestApiResponseFactory::fromArray([]),
            ),
            marketApi: new MarketApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
        );

        $instrument = $session->getInstrument('GAZP@MISX', 'ACC-1');

        $this->assertSame('GAZP@MISX', $instrument->symbol());
        $this->assertSame('asset-2', $instrument->id());
        $this->assertSame('GAZP', $instrument->ticker());
        $this->assertSame('MISX', $instrument->mic());
        $this->assertSame('ASSET_TYPE_STOCK', $instrument->type());
        $this->assertSame('Gazprom PJSC', $instrument->name());
        $this->assertSame('TQBR', $instrument->board());
        $this->assertSame(2, $instrument->decimals());
        $this->assertSame('0.01', $instrument->minStep());
        $this->assertSame('RUB', $instrument->quoteCurrency());
        $this->assertSame('Gazprom PJSC', $instrument->shortName());
    }

    public function testGetInstrumentBuildsSymbolFromTickerAndMicWhenPayloadOmitsIt(): void
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
                assetsResponse: TestApiResponseFactory::fromArray([]),
                assetResponse: TestApiResponseFactory::fromArray([
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'id' => 'asset-3',
                        'ticker' => 'SBER',
                        'mic' => 'MISX',
                        'type' => 'ASSET_TYPE_STOCK',
                        'name' => 'Sberbank',
                    ],
                    'error' => null,
                    'meta' => [],
                ]),
                allAssetsResponse: TestApiResponseFactory::fromArray([]),
            ),
            marketApi: new MarketApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
        );

        $instrument = $session->getInstrument('SBER@MISX', 'ACC-1');

        $this->assertSame('SBER@MISX', $instrument->symbol());
        $this->assertSame('SBER', $instrument->ticker());
        $this->assertSame('MISX', $instrument->mic());
    }

    public function testGetExchangesReturnsTypedCollection(): void
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
                assetsResponse: TestApiResponseFactory::fromArray([]),
                assetResponse: TestApiResponseFactory::fromArray([]),
                allAssetsResponse: TestApiResponseFactory::fromArray([]),
                exchangesResponse: TestApiResponseFactory::fromArray([
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'exchanges' => [
                            [
                                'mic' => 'MISX',
                                'name' => 'MOSCOW EXCHANGE - ALL MARKETS',
                            ],
                        ],
                    ],
                    'error' => null,
                    'meta' => [],
                ]),
            ),
            marketApi: new MarketApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
        );

        $exchanges = $session->getExchanges();

        /** @var ExchangeDto|null $firstExchange */
        $firstExchange = $exchanges->first();

        $this->assertInstanceOf(ExchangeCollection::class, $exchanges);
        $this->assertNotNull($firstExchange);
        $this->assertSame('MISX', $firstExchange->mic());
        $this->assertSame('MOSCOW EXCHANGE - ALL MARKETS', $firstExchange->name());
        $this->assertSame('MISX', $exchanges->findByMic('MISX')?->mic());
    }

    public function testGetClockReturnsDto(): void
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
                assetsResponse: TestApiResponseFactory::fromArray([]),
                assetResponse: TestApiResponseFactory::fromArray([]),
                allAssetsResponse: TestApiResponseFactory::fromArray([]),
                clockResponse: TestApiResponseFactory::fromArray([
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'timestamp' => '2026-04-01T09:00:00.123Z',
                    ],
                    'error' => null,
                    'meta' => [],
                ]),
            ),
            marketApi: new MarketApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
        );

        $clock = $session->getClock();

        $this->assertInstanceOf(ClockDto::class, $clock);
        $this->assertSame(
            '2026-04-01T09:00:00.123+00:00',
            $clock->timestamp()->format('Y-m-d\TH:i:s.vP'),
        );
    }

    public function testGetScheduleReturnsDto(): void
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
                assetsResponse: TestApiResponseFactory::fromArray([]),
                assetResponse: TestApiResponseFactory::fromArray([]),
                allAssetsResponse: TestApiResponseFactory::fromArray([]),
                scheduleResponse: TestApiResponseFactory::fromArray([
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'symbol' => 'YDEX@MISX',
                        'sessions' => [
                            [
                                'type' => 'CORE_TRADING',
                                'interval' => [
                                    'start_time' => '2026-04-01T10:00:00Z',
                                    'end_time' => '2026-04-01T12:00:00Z',
                                ],
                            ],
                        ],
                    ],
                    'error' => null,
                    'meta' => [],
                ]),
            ),
            marketApi: new MarketApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
        );

        $schedule = $session->getSchedule('YDEX@MISX');

        /** @var ScheduleSessionDto|null $coreTradingSession */
        $coreTradingSession = $schedule->sessions()->firstByType('CORE_TRADING');

        $this->assertInstanceOf(ScheduleDto::class, $schedule);
        $this->assertInstanceOf(ScheduleSessionCollection::class, $schedule->sessions());
        $this->assertNotNull($coreTradingSession);
        $this->assertSame('YDEX@MISX', $schedule->symbol());
        $this->assertSame('CORE_TRADING', $coreTradingSession->type());
        $this->assertEquals(
            new DateTimeImmutable('2026-04-01T10:00:00+00:00'),
            $coreTradingSession->startAt(),
        );
        $this->assertEquals(
            new DateTimeImmutable('2026-04-01T12:00:00+00:00'),
            $coreTradingSession->endAt(),
        );
    }

    public function testGetAllInstrumentsReturnsPageDto(): void
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
                assetsResponse: TestApiResponseFactory::fromArray([]),
                assetResponse: TestApiResponseFactory::fromArray([]),
                allAssetsResponse: TestApiResponseFactory::fromArray([
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'assets' => [
                            [
                                'id' => 'asset-3',
                                'symbol' => 'LKOH@MISX',
                                'ticker' => 'LKOH',
                                'mic' => 'MISX',
                                'type' => 'ASSET_TYPE_STOCK',
                                'name' => 'Lukoil',
                            ],
                        ],
                        'next_cursor' => '43',
                    ],
                    'error' => null,
                    'meta' => [],
                ]),
            ),
            marketApi: new MarketApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
        );

        $page = $session->getAllInstruments(cursor: 42, onlyActive: true);

        /** @var InstrumentDto|null $firstInstrument */
        $firstInstrument = $page->assets()->first();

        $this->assertInstanceOf(AllAssetsPageDto::class, $page);
        $this->assertNotNull($firstInstrument);
        $this->assertSame(43, $page->nextCursor());
        $this->assertTrue($page->hasNextPage());
        $this->assertSame('LKOH@MISX', $firstInstrument->symbol());
    }
}
