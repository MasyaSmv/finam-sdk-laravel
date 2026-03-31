<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Collections\InstrumentCollection;
use MasyaSmv\FinamSdk\Dto\Instrument\InstrumentDto;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

/**
 * @phpstan-import-type ApiMap from ApiValueReader
 * @psalm-import-type ApiMap from ApiValueReader
 */
final class InstrumentMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    /**
     * @param ApiMap $data
     */
    public function mapCollection(array $data): InstrumentCollection
    {
        $instruments = [];

        foreach ($this->reader->listOfArrays($data['assets'] ?? null, 'assets') as $asset) {
            $instruments[] = $this->map($asset);
        }

        /** @var list<InstrumentDto> $instruments */
        return new InstrumentCollection($instruments);
    }

    /**
     * @param ApiMap $data
     */
    public function map(array $data): InstrumentDto
    {
        /** @var ApiMap $instrumentData */
        $instrumentData = isset($data['asset']) && is_array($data['asset']) ? $data['asset'] : $data;

        return new InstrumentDto(
            symbol: $this->reader->requireString($instrumentData, 'symbol'),
            shortName: $this->reader->optionalString($instrumentData, 'short_name')
                ?? $this->reader->optionalString($instrumentData, 'name')
                ?? $this->reader->requireString($instrumentData, 'symbol'),
            description: $this->reader->optionalString($instrumentData, 'description'),
            market: $this->reader->optionalString($instrumentData, 'market'),
            currency: $this->reader->optionalString($instrumentData, 'currency'),
            lotSize: $this->reader->extractOptionalDecimalValue($instrumentData['lot_size'] ?? null, 'lot_size'),
            isin: $this->reader->optionalString($instrumentData, 'isin'),
        );
    }
}
