<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests\Support;

use MasyaSmv\FinamSdk\Contracts\Api\UsageMetricsApiInterface;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

final class UsageMetricsApiStub implements UsageMetricsApiInterface
{
    public function __construct(private ApiResponse $response)
    {
    }

    public function getUsageMetrics(): ApiResponse
    {
        return $this->response;
    }
}
