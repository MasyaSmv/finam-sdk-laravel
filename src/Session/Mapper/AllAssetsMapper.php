<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Dto\Instrument\AllAssetsPageDto;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

final class AllAssetsMapper
{
    public function __construct(
        private ApiValueReader $reader,
        private InstrumentMapper $instrumentMapper,
    ) {
    }

    public function map(ApiPayload $data): AllAssetsPageDto
    {
        return new AllAssetsPageDto(
            assets: $this->instrumentMapper->mapCollection($data),
            nextCursor: $this->reader->optionalInt($data, 'next_cursor'),
        );
    }
}
