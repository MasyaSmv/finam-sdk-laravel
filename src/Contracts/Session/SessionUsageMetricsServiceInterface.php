<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Session;

use MasyaSmv\FinamSdk\Dto\UsageMetrics\UsageMetricsDto;

interface SessionUsageMetricsServiceInterface
{
    public function getUsageMetrics(): UsageMetricsDto;
}
