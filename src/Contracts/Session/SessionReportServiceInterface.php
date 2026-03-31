<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Session;

use MasyaSmv\FinamSdk\Dto\Report\AccountReportInfoDto;
use MasyaSmv\FinamSdk\Dto\Report\CreateAccountReportInputDto;
use MasyaSmv\FinamSdk\Dto\Report\CreatedAccountReportDto;

interface SessionReportServiceInterface
{
    public function createAccountReport(CreateAccountReportInputDto $report): CreatedAccountReportDto;

    public function getAccountReportInfo(string $reportId): AccountReportInfoDto;
}
