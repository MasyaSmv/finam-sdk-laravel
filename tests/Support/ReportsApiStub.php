<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests\Support;

use MasyaSmv\FinamSdk\Contracts\Api\ReportsApiInterface;
use MasyaSmv\FinamSdk\Dto\Report\CreateAccountReportRequest;
use MasyaSmv\FinamSdk\Dto\Report\GetAccountReportInfoRequest;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

final class ReportsApiStub implements ReportsApiInterface
{
    public function __construct(
        private ApiResponse $createResponse,
        private ApiResponse $infoResponse,
    ) {
    }

    public function createAccountReport(CreateAccountReportRequest $request): ApiResponse
    {
        return $this->createResponse;
    }

    public function getAccountReportInfo(GetAccountReportInfoRequest $request): ApiResponse
    {
        return $this->infoResponse;
    }
}
