<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Api\Instrument;

use MasyaSmv\FinamSdk\Contracts\Api\InstrumentApiInterface;
use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Dto\Instrument\AssetsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ClockRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ExchangesRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\GetAssetParamsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\GetAssetRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\OptionsChainRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleRequest;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

/**
 * InstrumentApi — инструменты/справочники/поиск тикеров.
 */
final class InstrumentApi implements InstrumentApiInterface
{
    public function __construct(private FinamClient $client)
    {
    }

    /**
     * Список инструментов.
     */
    public function assets(AssetsRequest $request): ApiResponse
    {
        return $this->client->get('/assets', $request->toQuery());
    }

    /**
     * Время на сервере.
     */
    public function clock(ClockRequest $request): ApiResponse
    {
        return $this->client->get('/assets/clock', $request->toQuery());
    }

    /**
     * Список бирж.
     */
    public function exchanges(ExchangesRequest $request): ApiResponse
    {
        return $this->client->get('/assets/exchanges', $request->toQuery());
    }

    /**
     * Инструмент по символу.
     */
    public function asset(GetAssetRequest $request): ApiResponse
    {
        return $this->client->get('/assets/asset', $request->toQuery());
    }

    /**
     * Торговые параметры инструмента.
     */
    public function assetParams(GetAssetParamsRequest $request): ApiResponse
    {
        return $this->client->get("/assets/{$request->symbol()}/params", $request->toQuery());
    }

    /**
     * Цепочка опционов.
     */
    public function optionsChain(OptionsChainRequest $request): ApiResponse
    {
        return $this->client->get("/assets/{$request->underlyingSymbol()}/options", $request->toQuery());
    }

    /**
     * Расписание торгов по инструменту.
     */
    public function schedule(ScheduleRequest $request): ApiResponse
    {
        return $this->client->get('/assets/schedule', $request->toQuery());
    }
}
