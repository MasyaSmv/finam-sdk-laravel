<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Collections\TradeCollection;
use MasyaSmv\FinamSdk\Dto\Market\TradeDto;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

final class TradeMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    public function mapCollection(ApiPayload $data): TradeCollection
    {
        $symbol = $this->reader->requireString($data, 'symbol');
        $items = [];

        foreach ($this->reader->requireObjectList($data, 'trades')->payloads() as $trade) {
            $items[] = new TradeDto(
                symbol: $symbol,
                price: $this->reader->requireDecimal($trade, 'price'),
                quantity: $this->reader->requireDecimal($trade, 'quantity'),
                timestamp: $this->reader->optionalDateTime($trade, 'timestamp'),
                side: $this->reader->optionalString($trade, 'side'),
            );
        }

        /** @var list<TradeDto> $items */
        return new TradeCollection($items);
    }
}
