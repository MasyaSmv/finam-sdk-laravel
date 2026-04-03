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

    public function map(ApiPayload $data): QuoteDto
    {
        $quoteData = $this->reader->requireObject($data, 'quote');
        $symbol = $this->reader->optionalString($quoteData, 'symbol')
            ?? $this->reader->requireString($data, 'symbol');

        return new QuoteDto(
            symbol: $symbol,
            price: $this->reader->optionalDecimal($quoteData, 'last') ?? '0',
            change: $this->reader->optionalDecimal($quoteData, 'change'),
            percentChange: null,
            timestamp: $this->reader->optionalDateTime($quoteData, 'timestamp'),
        );
    }

    /**
     * @param list<QuoteDto> $quotes
     */
    public function mapCollection(array $quotes): QuoteCollection
    {
        return new QuoteCollection($quotes);
    }
}
