<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Report;

use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;

final class AccountReportInfoDto
{
    public function __construct(private ApiPayload $details)
    {
    }

    public function details(): ApiPayload
    {
        return $this->details;
    }
}
