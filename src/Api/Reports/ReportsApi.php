<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Api\Reports;

use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Contracts\Api\ReportsApiInterface;
use MasyaSmv\FinamSdk\Dto\Report\CreateAccountReportRequest;
use MasyaSmv\FinamSdk\Dto\Report\GetAccountReportInfoRequest;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

final class ReportsApi implements ReportsApiInterface
{
    public function __construct(private FinamClient $client)
    {
    }

    public function createAccountReport(CreateAccountReportRequest $request): ApiResponse
    {
        return $this->client->post('/report', $request->toPayload());
    }

    public function getAccountReportInfo(GetAccountReportInfoRequest $request): ApiResponse
    {
        return $this->client->get(sprintf('/report/%s/info', $request->reportId()));
    }
}
