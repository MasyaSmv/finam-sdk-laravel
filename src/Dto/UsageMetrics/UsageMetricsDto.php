<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\UsageMetrics;

use MasyaSmv\FinamSdk\Collections\UsageQuotaCollection;

final class UsageMetricsDto
{
    public function __construct(private UsageQuotaCollection $quotas)
    {
    }

    public function quotas(): UsageQuotaCollection
    {
        return $this->quotas;
    }
}
