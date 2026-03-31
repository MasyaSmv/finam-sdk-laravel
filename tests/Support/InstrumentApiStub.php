<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests\Support;

use MasyaSmv\FinamSdk\Contracts\Api\InstrumentApiInterface;
use MasyaSmv\FinamSdk\Dto\Instrument\AssetsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ClockRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ExchangesRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\GetAssetParamsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\GetAssetRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\OptionsChainRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleRequest;

/**
 * @phpstan-type TestScalar null|bool|int|float|string
 * @phpstan-type TestNestedArray array<int|string, TestScalar|array<int|string, TestScalar|array<int|string, TestScalar|array<int|string, TestScalar>>>>
 * @phpstan-type TestResponse array<string, TestScalar|TestNestedArray>
 */
final class InstrumentApiStub implements InstrumentApiInterface
{
    /**
     * @param TestResponse $assetsResponse
     * @param TestResponse $assetResponse
     */
    public function __construct(
        private array $assetsResponse,
        private array $assetResponse,
    ) {
    }

    public function assets(AssetsRequest $request): array
    {
        return $this->assetsResponse;
    }

    public function clock(ClockRequest $request): array
    {
        return [];
    }

    public function exchanges(ExchangesRequest $request): array
    {
        return [];
    }

    public function asset(GetAssetRequest $request): array
    {
        return $this->assetResponse;
    }

    public function assetParams(GetAssetParamsRequest $request): array
    {
        return [];
    }

    public function optionsChain(OptionsChainRequest $request): array
    {
        return [];
    }

    public function schedule(ScheduleRequest $request): array
    {
        return [];
    }
}
