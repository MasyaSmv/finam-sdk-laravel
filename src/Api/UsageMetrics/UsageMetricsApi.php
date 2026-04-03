<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Api\UsageMetrics;

use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Contracts\Api\UsageMetricsApiInterface;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

final class UsageMetricsApi implements UsageMetricsApiInterface
{
    public function __construct(private FinamClient $client)
    {
    }

    public function getUsageMetrics(): ApiResponse
    {
        return $this->client->get('/usage');
    }
}
