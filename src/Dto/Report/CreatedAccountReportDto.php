<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Report;

final class CreatedAccountReportDto
{
    public function __construct(private string $reportId)
    {
    }

    public function reportId(): string
    {
        return $this->reportId;
    }
}
