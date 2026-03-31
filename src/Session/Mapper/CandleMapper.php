<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Collections\CandleCollection;
use MasyaSmv\FinamSdk\Dto\Market\CandleDto;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

/**
 * @phpstan-import-type ApiMap from ApiValueReader
 * @psalm-import-type ApiMap from ApiValueReader
 */
final class CandleMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    /**
     * @param ApiMap $data
     */
    public function mapCollection(array $data): CandleCollection
    {
        $candles = [];

        foreach ($this->reader->listOfArrays($data['candles'] ?? null, 'candles') as $candleData) {
            $candles[] = new CandleDto(
                timestamp: $this->reader->parseDateTime($this->reader->requireString($candleData, 'timestamp'), 'timestamp'),
                open: $this->reader->extractDecimalValue($this->reader->requireArray($candleData, 'open'), 'open'),
                high: $this->reader->extractDecimalValue($this->reader->requireArray($candleData, 'high'), 'high'),
                low: $this->reader->extractDecimalValue($this->reader->requireArray($candleData, 'low'), 'low'),
                close: $this->reader->extractDecimalValue($this->reader->requireArray($candleData, 'close'), 'close'),
                volume: $this->reader->extractOptionalDecimalValue($candleData['volume'] ?? null, 'volume'),
            );
        }

        /** @var list<CandleDto> $candles */
        return new CandleCollection($candles);
    }
}
