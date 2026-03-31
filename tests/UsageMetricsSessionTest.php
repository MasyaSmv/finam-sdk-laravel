<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use MasyaSmv\FinamSdk\Collections\UsageQuotaCollection;
use MasyaSmv\FinamSdk\Dto\UsageMetrics\UsageMetricsDto;
use MasyaSmv\FinamSdk\Dto\UsageMetrics\UsageQuotaDto;
use MasyaSmv\FinamSdk\Session\FinamSession;
use MasyaSmv\FinamSdk\Tests\Support\AccountApiStub;
use MasyaSmv\FinamSdk\Tests\Support\ConnectApiStub;
use MasyaSmv\FinamSdk\Tests\Support\InstrumentApiStub;
use MasyaSmv\FinamSdk\Tests\Support\MarketApiStub;
use MasyaSmv\FinamSdk\Tests\Support\OrderApiStub;
use MasyaSmv\FinamSdk\Tests\Support\TestApiResponseFactory;
use MasyaSmv\FinamSdk\Tests\Support\UsageMetricsApiStub;

final class UsageMetricsSessionTest extends TestCase
{
    public function testGetUsageMetricsReturnsTypedDto(): void
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
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
            usageMetricsApi: new UsageMetricsApiStub(TestApiResponseFactory::fromArray([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'quotas' => [
                        [
                            'name' => 'marketdata_quotes',
                            'limit' => ['value' => '1000'],
                            'used' => ['value' => '125'],
                        ],
                    ],
                ],
                'error' => null,
                'meta' => [],
            ])),
        );

        $usageMetrics = $session->getUsageMetrics();

        /** @var UsageQuotaDto|null $firstQuota */
        $firstQuota = $usageMetrics->quotas()->first();

        $this->assertInstanceOf(UsageMetricsDto::class, $usageMetrics);
        $this->assertInstanceOf(UsageQuotaCollection::class, $usageMetrics->quotas());
        $this->assertNotNull($firstQuota);
        $this->assertSame('marketdata_quotes', $firstQuota->details()->string('name'));
        $this->assertSame('1000', $firstQuota->details()->decimalString('limit'));
        $this->assertSame('125', $firstQuota->details()->decimalString('used'));
    }
}
