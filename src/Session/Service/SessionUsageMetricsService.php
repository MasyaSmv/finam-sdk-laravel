<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Service;

use MasyaSmv\FinamSdk\Contracts\Api\UsageMetricsApiInterface;
use MasyaSmv\FinamSdk\Contracts\Session\ApiResponseDecoderInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionUsageMetricsServiceInterface;
use MasyaSmv\FinamSdk\Dto\UsageMetrics\UsageMetricsDto;
use MasyaSmv\FinamSdk\Session\Mapper\UsageMetricsMapper;

final class SessionUsageMetricsService implements SessionUsageMetricsServiceInterface
{
    public function __construct(
        private UsageMetricsApiInterface $usageMetricsApi,
        private ApiResponseDecoderInterface $decoder,
        private UsageMetricsMapper $mapper,
    ) {
    }

    public function getUsageMetrics(): UsageMetricsDto
    {
        $response = $this->usageMetricsApi->getUsageMetrics();
        $data = $this->decoder->extractData($response, 'usage');

        return $this->mapper->map($data);
    }
}
