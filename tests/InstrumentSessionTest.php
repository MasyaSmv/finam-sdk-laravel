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

final class InstrumentSessionTest extends TestCase
{
    public function testGetInstrumentsReturnsTypedCollection(): void
    {
        $session = FinamSession::fromApis(
            connectApi: new ConnectApiStub([]),
            accountApi: new AccountApiStub([]),
            orderApi: new OrderApiStub([], [], []),
            instrumentApi: new InstrumentApiStub(
                assetsResponse: [
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'assets' => [
                            [
                                'symbol' => 'SBER@MISX',
                                'short_name' => 'Sber',
                                'description' => 'Sberbank',
                                'market' => 'MISX',
                                'currency' => 'RUB',
                                'lot_size' => ['value' => '10'],
                                'isin' => 'RU0009029540',
                            ],
                        ],
                    ],
                    'error' => null,
                    'meta' => [],
                ],
                assetResponse: [],
            ),
            marketApi: new MarketApiStub([], []),
        );

        $instruments = $session->getInstruments();

        /** @var InstrumentDto|null $firstInstrument */
        $firstInstrument = $instruments->first();

        $this->assertInstanceOf(InstrumentCollection::class, $instruments);
        $this->assertSame('SBER@MISX', $firstInstrument?->symbol());
        $this->assertSame('SBER@MISX', $instruments->findBySymbol('SBER@MISX')?->symbol());
    }

    public function testGetInstrumentReturnsDto(): void
    {
        $session = FinamSession::fromApis(
            connectApi: new ConnectApiStub([]),
            accountApi: new AccountApiStub([]),
            orderApi: new OrderApiStub([], [], []),
            instrumentApi: new InstrumentApiStub(
                assetsResponse: [],
                assetResponse: [
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'asset' => [
                            'symbol' => 'GAZP@MISX',
                            'short_name' => 'Gazprom',
                            'description' => 'Gazprom PJSC',
                            'market' => 'MISX',
                            'currency' => 'RUB',
                        ],
                    ],
                    'error' => null,
                    'meta' => [],
                ],
            ),
            marketApi: new MarketApiStub([], []),
        );

        $instrument = $session->getInstrument('GAZP@MISX');

        $this->assertSame('GAZP@MISX', $instrument->symbol());
        $this->assertSame('Gazprom', $instrument->shortName());
    }
}
