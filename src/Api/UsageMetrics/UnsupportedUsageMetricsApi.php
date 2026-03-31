<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Api\UsageMetrics;

use LogicException;
use MasyaSmv\FinamSdk\Contracts\Api\UsageMetricsApiInterface;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

final class UnsupportedUsageMetricsApi implements UsageMetricsApiInterface
{
    public function getUsageMetrics(): ApiResponse
    {
        throw new LogicException('UsageMetricsApi is not configured for this session instance.');
    }
}
