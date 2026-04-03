<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Collections;

use Illuminate\Support\Collection;
use MasyaSmv\FinamSdk\Dto\UsageMetrics\UsageQuotaDto;

final class UsageQuotaCollection extends Collection
{
    /**
     * @param list<UsageQuotaDto> $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }
}
