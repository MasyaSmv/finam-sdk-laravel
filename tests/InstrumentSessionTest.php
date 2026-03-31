<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use MasyaSmv\FinamSdk\Collections\InstrumentCollection;
use MasyaSmv\FinamSdk\Dto\Instrument\InstrumentDto;
use MasyaSmv\FinamSdk\Session\FinamSession;
use MasyaSmv\FinamSdk\Tests\Support\AccountApiStub;
use MasyaSmv\FinamSdk\Tests\Support\ConnectApiStub;
use MasyaSmv\FinamSdk\Tests\Support\InstrumentApiStub;
use MasyaSmv\FinamSdk\Tests\Support\MarketApiStub;
use MasyaSmv\FinamSdk\Tests\Support\OrderApiStub;
use MasyaSmv\FinamSdk\Tests\Support\TestApiResponseFactory;

final class InstrumentSessionTest extends TestCase
{
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
                assetsResponse: TestApiResponseFactory::fromArray([
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
                assetResponse: TestApiResponseFactory::fromArray([]),
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
            ),
            marketApi: new MarketApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
        );

        $instrument = $session->getInstrument('GAZP@MISX');

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
}
