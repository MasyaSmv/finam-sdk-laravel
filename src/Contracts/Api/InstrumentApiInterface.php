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
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

interface InstrumentApiInterface
{
    public function assets(AssetsRequest $request): ApiResponse;

    public function clock(ClockRequest $request): ApiResponse;

    public function exchanges(ExchangesRequest $request): ApiResponse;

    public function asset(GetAssetRequest $request): ApiResponse;

    public function assetParams(GetAssetParamsRequest $request): ApiResponse;

    public function optionsChain(OptionsChainRequest $request): ApiResponse;

    public function schedule(ScheduleRequest $request): ApiResponse;
}
