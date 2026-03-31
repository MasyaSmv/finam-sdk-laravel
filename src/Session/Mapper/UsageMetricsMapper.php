<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Collections\UsageQuotaCollection;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Dto\UsageMetrics\UsageMetricsDto;
use MasyaSmv\FinamSdk\Dto\UsageMetrics\UsageQuotaDto;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

final class UsageMetricsMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    public function map(ApiPayload $data): UsageMetricsDto
    {
        $quotas = [];

        foreach ($this->reader->requireObjectList($data, 'quotas')->payloads() as $quotaData) {
            $quotas[] = new UsageQuotaDto($quotaData);
        }

        /** @var list<UsageQuotaDto> $quotas */
        return new UsageMetricsDto(new UsageQuotaCollection($quotas));
    }
}
