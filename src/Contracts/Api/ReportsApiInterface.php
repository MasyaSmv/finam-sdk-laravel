<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Api;

use MasyaSmv\FinamSdk\Dto\Report\CreateAccountReportRequest;
use MasyaSmv\FinamSdk\Dto\Report\GetAccountReportInfoRequest;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

interface ReportsApiInterface
{
    public function createAccountReport(CreateAccountReportRequest $request): ApiResponse;

    public function getAccountReportInfo(GetAccountReportInfoRequest $request): ApiResponse;
}
