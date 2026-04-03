<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Dto\Report\AccountReportInfoDto;
use MasyaSmv\FinamSdk\Dto\Report\CreatedAccountReportDto;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

final class ReportMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    public function mapCreatedReport(ApiPayload $data): CreatedAccountReportDto
    {
        return new CreatedAccountReportDto(
            reportId: $this->reader->requireString($data, 'report_id'),
        );
    }

    public function mapReportInfo(ApiPayload $data): AccountReportInfoDto
    {
        $reportInfo = $this->reader->optionalObject($data, 'info') ?? $data;

        return new AccountReportInfoDto($reportInfo);
    }
}
