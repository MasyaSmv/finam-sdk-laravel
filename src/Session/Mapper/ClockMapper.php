<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Dto\Instrument\ClockDto;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

final class ClockMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    public function map(ApiPayload $data): ClockDto
    {
        return new ClockDto(
            timestamp: $this->reader->requireTimestamp($data, 'timestamp'),
        );
    }
}
