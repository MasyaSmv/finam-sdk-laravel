<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use LogicException;
use MasyaSmv\FinamSdk\Api\Account\AccountApi;
use MasyaSmv\FinamSdk\Api\Auth\AuthApi;
use MasyaSmv\FinamSdk\Api\Connect\ConnectApi;
use MasyaSmv\FinamSdk\Api\Instrument\InstrumentApi;
use MasyaSmv\FinamSdk\Api\Market\MarketApi;
use MasyaSmv\FinamSdk\Api\Order\OrderApi;
use MasyaSmv\FinamSdk\Api\Reports\ReportsApi;
use MasyaSmv\FinamSdk\Api\Reports\UnsupportedReportsApi;
use MasyaSmv\FinamSdk\Api\UsageMetrics\UnsupportedUsageMetricsApi;
use MasyaSmv\FinamSdk\Api\UsageMetrics\UsageMetricsApi;
use MasyaSmv\FinamSdk\Dto\Account\GetAccountRequest;
use MasyaSmv\FinamSdk\Dto\Account\TradesRequest;
use MasyaSmv\FinamSdk\Dto\Account\TransactionsRequest;
use MasyaSmv\FinamSdk\Dto\Auth\AuthRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\AllAssetsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\AssetsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ClockRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ExchangesRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\GetAssetParamsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\GetAssetRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\OptionsChainRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleRequest;
use MasyaSmv\FinamSdk\Dto\Market\CandlesQueryDto;
use MasyaSmv\FinamSdk\Dto\Market\CandlesRequest;
use MasyaSmv\FinamSdk\Dto\Market\OrderbookRequest;
use MasyaSmv\FinamSdk\Dto\Market\QuotesRequest;
use MasyaSmv\FinamSdk\Dto\Market\TradesRequest as MarketTradesRequest;
use MasyaSmv\FinamSdk\Dto\Order\CancelOrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrdersRequest;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderInputDto;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\PlaceSlTpOrderInputDto;
use MasyaSmv\FinamSdk\Dto\Order\PlaceSlTpOrderRequest;
use MasyaSmv\FinamSdk\Dto\Report\CreateAccountReportInputDto;
use MasyaSmv\FinamSdk\Dto\Report\CreateAccountReportRequest;
use MasyaSmv\FinamSdk\Dto\Report\GetAccountReportInfoRequest;
use MasyaSmv\FinamSdk\Dto\Report\ReportDateRangeDto;
use MasyaSmv\FinamSdk\Dto\Shared\Interval;
use MasyaSmv\FinamSdk\Tests\Support\HttpClientTestHelper;

final class ApiWrapperCoverageTest extends TestCase
{
    use HttpClientTestHelper;

    public function testAccountApiBuildsExpectedEndpoints(): void
    {
        $history = [];
        $client = $this->makeClientWithQueue([
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
        ], $history);

        $api = new AccountApi($client);

        $api->account(new GetAccountRequest('ACC-1'));
        $api->trades(new TradesRequest('ACC-1', 5, new Interval(1774933200, 1775019600)));
        $api->transactions(new TransactionsRequest('ACC-1', 10, new Interval(1774933200, 1775019600)));

        $this->assertCount(3, $history);
        $this->assertSame('/accounts/ACC-1', $history[0]['request']->getUri()->getPath());
        $this->assertSame('/accounts/ACC-1/trades', $history[1]['request']->getUri()->getPath());
        $this->assertSame('limit=5&interval.startTime=2026-03-31T05%3A00%3A00Z&interval.endTime=2026-04-01T05%3A00%3A00Z', $history[1]['request']->getUri()->getQuery());
        $this->assertSame('/accounts/ACC-1/transactions', $history[2]['request']->getUri()->getPath());
        $this->assertSame('limit=10&interval.startTime=2026-03-31T05%3A00%3A00Z&interval.endTime=2026-04-01T05%3A00%3A00Z', $history[2]['request']->getUri()->getQuery());
    }

    public function testConnectAuthUsageAndReportsApisBuildExpectedRequests(): void
    {
        $history = [];
        $client = $this->makeClientWithQueue([
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
        ], $history, 'jwt-token');

        (new ConnectApi($client))->tokenDetails();
        (new AuthApi($client))->issueToken(new AuthRequest('secret'));
        (new UsageMetricsApi($client))->getUsageMetrics();

        $reportInput = new CreateAccountReportInputDto(
            'ACC-1',
            'REPORT_FORM_SAMPLE',
            new ReportDateRangeDto(new DateTimeImmutable('2026-03-01'), new DateTimeImmutable('2026-03-31')),
        );

        (new ReportsApi($client))->createAccountReport(new CreateAccountReportRequest($reportInput));
        (new ReportsApi($client))->getAccountReportInfo(new GetAccountReportInfoRequest('report-1'));

        $this->assertCount(5, $history);
        $this->assertSame('/sessions/details', $history[0]['request']->getUri()->getPath());
        $this->assertSame('{"token":"jwt-token"}', (string) $history[0]['request']->getBody());
        $this->assertSame('/sessions', $history[1]['request']->getUri()->getPath());
        $this->assertSame('{"secret":"secret"}', (string) $history[1]['request']->getBody());
        $this->assertSame('/usage', $history[2]['request']->getUri()->getPath());
        $this->assertSame('/report', $history[3]['request']->getUri()->getPath());
        $this->assertSame('/report/report-1/info', $history[4]['request']->getUri()->getPath());
    }

    public function testInstrumentApiBuildsExpectedRequests(): void
    {
        $history = [];
        $client = $this->makeClientWithQueue([
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
        ], $history);

        $api = new InstrumentApi($client);
        $api->allAssets(new AllAssetsRequest(12, true, false));
        $api->assets(new AssetsRequest());
        $api->clock(new ClockRequest());
        $api->exchanges(new ExchangesRequest());
        $api->asset(new GetAssetRequest('SBER@MISX', 'ACC-1'));
        $api->assetParams(new GetAssetParamsRequest('SBER@MISX', 'ACC-1'));
        $api->optionsChain(new OptionsChainRequest('SBER@MISX', 'SB', '2026-06-01'));
        $api->schedule(new ScheduleRequest('SBER@MISX'));

        $this->assertSame('/assets/all', $history[0]['request']->getUri()->getPath());
        $this->assertSame('cursor=12&only_active=1', $history[0]['request']->getUri()->getQuery());
        $this->assertSame('/assets', $history[1]['request']->getUri()->getPath());
        $this->assertSame('/assets/clock', $history[2]['request']->getUri()->getPath());
        $this->assertSame('/exchanges', $history[3]['request']->getUri()->getPath());
        $this->assertSame('/assets/SBER@MISX', $history[4]['request']->getUri()->getPath());
        $this->assertSame('account_id=ACC-1', $history[4]['request']->getUri()->getQuery());
        $this->assertSame('/assets/SBER@MISX/params', $history[5]['request']->getUri()->getPath());
        $this->assertSame('/assets/SBER@MISX/options', $history[6]['request']->getUri()->getPath());
        $this->assertSame('root=SB&expiration_date=2026-06-01', $history[6]['request']->getUri()->getQuery());
        $this->assertSame('/assets/SBER@MISX/schedule', $history[7]['request']->getUri()->getPath());
    }

    public function testMarketApiBuildsExpectedRequests(): void
    {
        $history = [];
        $client = $this->makeClientWithQueue([
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
        ], $history);

        $api = new MarketApi($client);
        $api->candles(new CandlesRequest(
            new CandlesQueryDto(
                'SBER@MISX',
                'TIME_FRAME_M1',
                new DateTimeImmutable('2026-03-31T10:00:00+03:00'),
                new DateTimeImmutable('2026-03-31T11:00:00+03:00'),
            ),
        ));
        $api->quotes(new QuotesRequest('SBER@MISX'));
        $api->orderbook(new OrderbookRequest('SBER@MISX'));
        $api->trades(new MarketTradesRequest('SBER@MISX'));

        $this->assertSame('/instruments/SBER@MISX/bars/', $history[0]['request']->getUri()->getPath());
        $this->assertSame('timeframe=TIME_FRAME_M1&interval.startTime=2026-03-31T07%3A00%3A00Z&interval.endTime=2026-03-31T08%3A00%3A00Z', $history[0]['request']->getUri()->getQuery());
        $this->assertSame('/instruments/SBER@MISX/quotes/latest', $history[1]['request']->getUri()->getPath());
        $this->assertSame('/instruments/SBER@MISX/orderbook', $history[2]['request']->getUri()->getPath());
        $this->assertSame('/instruments/SBER@MISX/trades/latest', $history[3]['request']->getUri()->getPath());
    }

    public function testOrderApiBuildsExpectedRequests(): void
    {
        $history = [];
        $client = $this->makeClientWithQueue([
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
        ], $history);

        $api = new OrderApi($client);
        $api->orders(new OrdersRequest('ACC-1'));
        $api->order(new OrderRequest('ACC-1', 'ORD-1'));
        $api->place(new PlaceOrderRequest('ACC-1', new PlaceOrderInputDto('SBER@MISX', '1', 'BUY', 'LIMIT', 'DAY')));
        $api->placeSlTp(new PlaceSlTpOrderRequest('ACC-1', new PlaceSlTpOrderInputDto('SBER@MISX', 'SELL', '1', '100.00')));
        $api->cancel(new CancelOrderRequest('ACC-1', 'ORD-1'));

        $this->assertSame('/accounts/ACC-1/orders', $history[0]['request']->getUri()->getPath());
        $this->assertSame('/accounts/ACC-1/orders/ORD-1', $history[1]['request']->getUri()->getPath());
        $this->assertSame('/accounts/ACC-1/orders', $history[2]['request']->getUri()->getPath());
        $this->assertSame('/accounts/ACC-1/sltp-orders', $history[3]['request']->getUri()->getPath());
        $this->assertSame('/accounts/ACC-1/orders/ORD-1', $history[4]['request']->getUri()->getPath());
        $this->assertSame('DELETE', $history[4]['request']->getMethod());
    }

    public function testUnsupportedApisThrowLogicException(): void
    {
        $reports = new UnsupportedReportsApi();
        $usage = new UnsupportedUsageMetricsApi();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('ReportsApi is not configured for this session instance.');
        $reports->createAccountReport(new CreateAccountReportRequest(
            new CreateAccountReportInputDto(
                'ACC-1',
                'REPORT_FORM_SAMPLE',
                new ReportDateRangeDto(new DateTimeImmutable('2026-03-01'), new DateTimeImmutable('2026-03-31')),
            ),
        ));

        $usage->getUsageMetrics();
    }

    public function testUnsupportedReportsApiGetInfoAlsoThrows(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('ReportsApi is not configured for this session instance.');

        (new UnsupportedReportsApi())->getAccountReportInfo(new GetAccountReportInfoRequest('report-1'));
    }

    public function testUnsupportedUsageMetricsApiThrows(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('UsageMetricsApi is not configured for this session instance.');

        (new UnsupportedUsageMetricsApi())->getUsageMetrics();
    }
}
