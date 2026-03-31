<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Report;

final class CreateAccountReportRequest
{
    public function __construct(private CreateAccountReportInputDto $payload)
    {
    }

    public function payload(): CreateAccountReportInputDto
    {
        return $this->payload;
    }

    /**
     * @return array{
     *     account_id: string,
     *     report_form: string,
     *     date_range: array{from: string, to: string}
     * }
     */
    public function toPayload(): array
    {
        return [
            'account_id' => $this->payload->accountId(),
            'report_form' => $this->payload->reportForm(),
            'date_range' => $this->payload->dateRange()->toPayload(),
        ];
    }
}
