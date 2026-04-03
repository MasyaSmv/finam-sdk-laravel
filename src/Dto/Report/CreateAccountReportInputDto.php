<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Report;

use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class CreateAccountReportInputDto
{
    public function __construct(
        private string $accountId,
        private string $reportForm,
        private ReportDateRangeDto $dateRange,
    ) {
        if ($this->accountId === '') {
            throw new InvalidRequestException('Account ID must not be empty.');
        }

        if ($this->reportForm === '') {
            throw new InvalidRequestException('Report form must not be empty.');
        }
    }

    public function accountId(): string
    {
        return $this->accountId;
    }

    public function reportForm(): string
    {
        return $this->reportForm;
    }

    public function dateRange(): ReportDateRangeDto
    {
        return $this->dateRange;
    }
}
