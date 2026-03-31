<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Api\Reports;

use LogicException;
use MasyaSmv\FinamSdk\Contracts\Api\ReportsApiInterface;
use MasyaSmv\FinamSdk\Dto\Report\CreateAccountReportRequest;
use MasyaSmv\FinamSdk\Dto\Report\GetAccountReportInfoRequest;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

final class UnsupportedReportsApi implements ReportsApiInterface
{
    public function createAccountReport(CreateAccountReportRequest $request): ApiResponse
    {
        throw new LogicException('ReportsApi is not configured for this session instance.');
    }

    public function getAccountReportInfo(GetAccountReportInfoRequest $request): ApiResponse
    {
        throw new LogicException('ReportsApi is not configured for this session instance.');
    }
}
