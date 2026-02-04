<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Api;

use MasyaSmv\FinamSdk\Dto\Instrument\AssetsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ClockRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ExchangesRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\GetAssetParamsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\GetAssetRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\OptionsChainRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleRequest;

interface InstrumentApiInterface
{
    /**
     * @return array<string, mixed>
     */
    public function assets(AssetsRequest $request): array;

    /**
     * @return array<string, mixed>
     */
    public function clock(ClockRequest $request): array;

    /**
     * @return array<string, mixed>
     */
    public function exchanges(ExchangesRequest $request): array;

    /**
     * @return array<string, mixed>
     */
    public function asset(GetAssetRequest $request): array;

    /**
     * @return array<string, mixed>
     */
    public function assetParams(GetAssetParamsRequest $request): array;

    /**
     * @return array<string, mixed>
     */
    public function optionsChain(OptionsChainRequest $request): array;

    /**
     * @return array<string, mixed>
     */
    public function schedule(ScheduleRequest $request): array;
}
