<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Collections\InstrumentCollection;
use MasyaSmv\FinamSdk\Dto\Instrument\InstrumentDto;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;
final class InstrumentMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    public function mapCollection(ApiPayload $data): InstrumentCollection
    {
        $instruments = [];

        foreach ($this->reader->requireObjectList($data, 'assets')->payloads() as $asset) {
            $instruments[] = $this->map($asset);
        }

        /** @var list<InstrumentDto> $instruments */
        return new InstrumentCollection($instruments);
    }

    public function map(ApiPayload $data): InstrumentDto
    {
        $instrumentData = $this->reader->optionalObject($data, 'asset') ?? $data;

        return new InstrumentDto(
            symbol: $this->reader->requireString($instrumentData, 'symbol'),
            id: $this->reader->optionalString($instrumentData, 'id'),
            ticker: $this->reader->optionalString($instrumentData, 'ticker'),
            mic: $this->reader->optionalString($instrumentData, 'mic')
                ?? $this->reader->optionalString($instrumentData, 'market'),
            type: $this->reader->optionalString($instrumentData, 'type'),
            name: $this->reader->optionalString($instrumentData, 'name')
                ?? $this->reader->optionalString($instrumentData, 'short_name'),
            board: $this->reader->optionalString($instrumentData, 'board'),
            decimals: $this->reader->optionalInt($instrumentData, 'decimals'),
            minStep: $this->reader->optionalDecimal($instrumentData, 'min_step'),
            quoteCurrency: $this->reader->optionalString($instrumentData, 'quote_currency')
                ?? $this->reader->optionalString($instrumentData, 'currency'),
            expirationDate: $this->reader->optionalString($instrumentData, 'expiration_date'),
            lotSize: $this->reader->optionalDecimal($instrumentData, 'lot_size'),
            isin: $this->reader->optionalString($instrumentData, 'isin'),
        );
    }
}
