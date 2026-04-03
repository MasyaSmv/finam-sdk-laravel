<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use DateTimeImmutable;
use MasyaSmv\FinamSdk\Dto\Report\AccountReportInfoDto;
use MasyaSmv\FinamSdk\Dto\Report\CreateAccountReportInputDto;
use MasyaSmv\FinamSdk\Dto\Report\CreatedAccountReportDto;
use MasyaSmv\FinamSdk\Dto\Report\ReportDateRangeDto;
use MasyaSmv\FinamSdk\Session\FinamSession;
use MasyaSmv\FinamSdk\Tests\Support\AccountApiStub;
use MasyaSmv\FinamSdk\Tests\Support\ConnectApiStub;
use MasyaSmv\FinamSdk\Tests\Support\InstrumentApiStub;
use MasyaSmv\FinamSdk\Tests\Support\MarketApiStub;
use MasyaSmv\FinamSdk\Tests\Support\OrderApiStub;
use MasyaSmv\FinamSdk\Tests\Support\ReportsApiStub;
use MasyaSmv\FinamSdk\Tests\Support\TestApiResponseFactory;

final class ReportSessionTest extends TestCase
{
    public function testCreateAccountReportReturnsTypedDto(): void
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
            reportsApi: new ReportsApiStub(
                createResponse: TestApiResponseFactory::fromArray([
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'report_id' => 'report-001',
                    ],
                    'error' => null,
                    'meta' => [],
                ]),
                infoResponse: TestApiResponseFactory::fromArray([]),
            ),
        );

        $report = $session->createAccountReport(
            new CreateAccountReportInputDto(
                accountId: '1899011',
                reportForm: 'REPORT_FORM_SAMPLE',
                dateRange: new ReportDateRangeDto(
                    from: new DateTimeImmutable('2026-03-01'),
                    to: new DateTimeImmutable('2026-03-31'),
                ),
            ),
        );

        $this->assertInstanceOf(CreatedAccountReportDto::class, $report);
        $this->assertSame('report-001', $report->reportId());
    }

    public function testGetAccountReportInfoReturnsTypedDto(): void
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
            reportsApi: new ReportsApiStub(
                createResponse: TestApiResponseFactory::fromArray([]),
                infoResponse: TestApiResponseFactory::fromArray([
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'info' => [
                            'status' => 'REPORT_STATUS_DONE',
                            'file_url' => 'https://download.example/report-001.xlsx',
                        ],
                    ],
                    'error' => null,
                    'meta' => [],
                ]),
            ),
        );

        $reportInfo = $session->getAccountReportInfo('report-001');

        $this->assertInstanceOf(AccountReportInfoDto::class, $reportInfo);
        $this->assertSame('REPORT_STATUS_DONE', $reportInfo->details()->string('status'));
        $this->assertSame(
            'https://download.example/report-001.xlsx',
            $reportInfo->details()->string('file_url'),
        );
    }
}
