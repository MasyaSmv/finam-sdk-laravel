<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\UsageMetrics;

use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;

final class UsageQuotaDto
{
    public function __construct(private ApiPayload $details)
    {
    }

    public function details(): ApiPayload
    {
        return $this->details;
    }
}
