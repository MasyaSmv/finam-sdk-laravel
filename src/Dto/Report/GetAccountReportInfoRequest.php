<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Report;

use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class GetAccountReportInfoRequest
{
    public function __construct(private string $reportId)
    {
        if ($this->reportId === '') {
            throw new InvalidRequestException('Report ID must not be empty.');
        }
    }

    public function reportId(): string
    {
        return $this->reportId;
    }
}
