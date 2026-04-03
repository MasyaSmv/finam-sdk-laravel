<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Api;

use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

interface UsageMetricsApiInterface
{
    public function getUsageMetrics(): ApiResponse;
}
