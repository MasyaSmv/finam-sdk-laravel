<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Collections\QuoteCollection;
use MasyaSmv\FinamSdk\Dto\Market\QuoteDto;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

/**
 * @phpstan-import-type ApiMap from ApiValueReader
 * @psalm-import-type ApiMap from ApiValueReader
 */
final class QuoteMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    /**
     * @param ApiMap $data
     */
    public function mapCollection(array $data): QuoteCollection
    {
        $quotes = [];

        foreach ($this->reader->listOfArrays($data['quotes'] ?? null, 'quotes') as $quoteData) {
            $quotes[] = new QuoteDto(
                symbol: $this->reader->requireString($quoteData, 'symbol'),
                price: $this->reader->extractOptionalDecimalValue($quoteData['price'] ?? null, 'price') ?? '0',
                change: $this->reader->extractOptionalDecimalValue($quoteData['change'] ?? null, 'change'),
                percentChange: $this->reader->extractOptionalDecimalValue($quoteData['change_percent'] ?? null, 'change_percent'),
                timestamp: $this->reader->optionalDateTime($quoteData, 'timestamp'),
            );
        }

        /** @var list<QuoteDto> $quotes */
        return new QuoteCollection($quotes);
    }
}
