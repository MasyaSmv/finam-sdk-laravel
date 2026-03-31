<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Collections\QuoteCollection;
use MasyaSmv\FinamSdk\Dto\Market\QuoteDto;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;
final class QuoteMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    public function mapCollection(ApiPayload $data): QuoteCollection
    {
        $quotes = [];

        foreach ($this->reader->requireObjectList($data, 'quotes')->payloads() as $quoteData) {
            $quotes[] = new QuoteDto(
                symbol: $this->reader->requireString($quoteData, 'symbol'),
                price: $this->reader->optionalDecimal($quoteData, 'price') ?? '0',
                change: $this->reader->optionalDecimal($quoteData, 'change'),
                percentChange: $this->reader->optionalDecimal($quoteData, 'change_percent'),
                timestamp: $this->reader->optionalDateTime($quoteData, 'timestamp'),
            );
        }

        /** @var list<QuoteDto> $quotes */
        return new QuoteCollection($quotes);
    }
}
