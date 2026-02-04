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
     *
     * @param AssetsRequest $request
     *
     * @return array<string, mixed>
     */
    public function assets(AssetsRequest $request): array
    {
        return $this->client->get('/assets', $request->toQuery());
    }

    /**
     * Время на сервере.
     *
     * @param ClockRequest $request
     *
     * @return array<string, mixed>
     */
    public function clock(ClockRequest $request): array
    {
        return $this->client->get('/assets/clock', $request->toQuery());
    }

    /**
     * Список бирж.
     *
     * @param ExchangesRequest $request
     *
     * @return array<string, mixed>
     */
    public function exchanges(ExchangesRequest $request): array
    {
        return $this->client->get('/assets/exchanges', $request->toQuery());
    }

    /**
     * Инструмент по символу.
     *
     * @param GetAssetRequest $request
     *
     * @return array<string, mixed>
     */
    public function asset(GetAssetRequest $request): array
    {
        return $this->client->get('/assets/asset', $request->toQuery());
    }

    /**
     * Торговые параметры инструмента.
     *
     * @param GetAssetParamsRequest $request
     *
     * @return array<string, mixed>
     */
    public function assetParams(GetAssetParamsRequest $request): array
    {
        return $this->client->get('/assets/asset/params', $request->toQuery());
    }

    /**
     * Цепочка опционов.
     *
     * @param OptionsChainRequest $request
     *
     * @return array<string, mixed>
     */
    public function optionsChain(OptionsChainRequest $request): array
    {
        return $this->client->get('/assets/options/chain', $request->toQuery());
    }

    /**
     * Расписание торгов по инструменту.
     *
     * @param ScheduleRequest $request
     *
     * @return array<string, mixed>
     */
    public function schedule(ScheduleRequest $request): array
    {
        return $this->client->get('/assets/schedule', $request->toQuery());
    }
}
