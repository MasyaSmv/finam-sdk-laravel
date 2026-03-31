<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Service;

use MasyaSmv\FinamSdk\Contracts\Api\ReportsApiInterface;
use MasyaSmv\FinamSdk\Contracts\Session\ApiResponseDecoderInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionReportServiceInterface;
use MasyaSmv\FinamSdk\Dto\Report\AccountReportInfoDto;
use MasyaSmv\FinamSdk\Dto\Report\CreateAccountReportInputDto;
use MasyaSmv\FinamSdk\Dto\Report\CreateAccountReportRequest;
use MasyaSmv\FinamSdk\Dto\Report\CreatedAccountReportDto;
use MasyaSmv\FinamSdk\Dto\Report\GetAccountReportInfoRequest;
use MasyaSmv\FinamSdk\Session\Mapper\ReportMapper;

final class SessionReportService implements SessionReportServiceInterface
{
    public function __construct(
        private ReportsApiInterface $reportsApi,
        private ApiResponseDecoderInterface $decoder,
        private ReportMapper $mapper,
    ) {
    }

    public function createAccountReport(CreateAccountReportInputDto $report): CreatedAccountReportDto
    {
        $response = $this->reportsApi->createAccountReport(new CreateAccountReportRequest($report));
        $data = $this->decoder->extractData($response, 'report');

        return $this->mapper->mapCreatedReport($data);
    }

    public function getAccountReportInfo(string $reportId): AccountReportInfoDto
    {
        $response = $this->reportsApi->getAccountReportInfo(new GetAccountReportInfoRequest($reportId));
        $data = $this->decoder->extractData($response, 'report/info');

        return $this->mapper->mapReportInfo($data);
    }
}
