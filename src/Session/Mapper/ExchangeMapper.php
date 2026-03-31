<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Collections\ExchangeCollection;
use MasyaSmv\FinamSdk\Dto\Instrument\ExchangeDto;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

final class ExchangeMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    public function mapCollection(ApiPayload $data): ExchangeCollection
    {
        $exchanges = [];

        foreach ($this->reader->requireObjectList($data, 'exchanges')->payloads() as $exchangeData) {
            $exchanges[] = new ExchangeDto(
                mic: $this->reader->requireString($exchangeData, 'mic'),
                name: $this->reader->requireString($exchangeData, 'name'),
            );
        }

        /** @var list<ExchangeDto> $exchanges */
        return new ExchangeCollection($exchanges);
    }
}
