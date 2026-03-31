<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Collections\CandleCollection;
use MasyaSmv\FinamSdk\Dto\Market\CandleDto;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;
final class CandleMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    public function mapCollection(ApiPayload $data): CandleCollection
    {
        $candles = [];

        foreach ($this->reader->requireObjectList($data, 'candles')->payloads() as $candleData) {
            $candles[] = new CandleDto(
                timestamp: $this->reader->parseDateTime($this->reader->requireString($candleData, 'timestamp'), 'timestamp'),
                open: $this->reader->requireDecimal($candleData, 'open'),
                high: $this->reader->requireDecimal($candleData, 'high'),
                low: $this->reader->requireDecimal($candleData, 'low'),
                close: $this->reader->requireDecimal($candleData, 'close'),
                volume: $this->reader->optionalDecimal($candleData, 'volume'),
            );
        }

        /** @var list<CandleDto> $candles */
        return new CandleCollection($candles);
    }
}
