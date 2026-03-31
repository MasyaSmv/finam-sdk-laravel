<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests\Support;

use MasyaSmv\FinamSdk\Contracts\Api\InstrumentApiInterface;
use MasyaSmv\FinamSdk\Dto\Instrument\AllAssetsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\AssetsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ClockRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ExchangesRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\GetAssetParamsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\GetAssetRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\OptionsChainRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleRequest;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

final class InstrumentApiStub implements InstrumentApiInterface
{
    public function __construct(
        private ApiResponse $assetsResponse,
        private ApiResponse $assetResponse,
        private ?ApiResponse $allAssetsResponse = null,
        private ?ApiResponse $clockResponse = null,
        private ?ApiResponse $exchangesResponse = null,
        private ?ApiResponse $scheduleResponse = null,
    ) {
    }

    public function allAssets(AllAssetsRequest $request): ApiResponse
    {
        return $this->allAssetsResponse ?? TestApiResponseFactory::fromArray([]);
    }

    public function assets(AssetsRequest $request): ApiResponse
    {
        return $this->assetsResponse;
    }

    public function clock(ClockRequest $request): ApiResponse
    {
        return $this->clockResponse ?? TestApiResponseFactory::fromArray([]);
    }

    public function exchanges(ExchangesRequest $request): ApiResponse
    {
        return $this->exchangesResponse ?? TestApiResponseFactory::fromArray([]);
    }

    public function asset(GetAssetRequest $request): ApiResponse
    {
        return $this->assetResponse;
    }

    public function assetParams(GetAssetParamsRequest $request): ApiResponse
    {
        return TestApiResponseFactory::fromArray([]);
    }

    public function optionsChain(OptionsChainRequest $request): ApiResponse
    {
        return TestApiResponseFactory::fromArray([]);
    }

    public function schedule(ScheduleRequest $request): ApiResponse
    {
        return $this->scheduleResponse ?? TestApiResponseFactory::fromArray([]);
    }
}
