<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Collections\OrderBookLevelCollection;
use MasyaSmv\FinamSdk\Dto\Market\OrderBookDto;
use MasyaSmv\FinamSdk\Dto\Market\OrderBookLevelDto;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

final class OrderBookMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    public function map(ApiPayload $data): OrderBookDto
    {
        $orderBook = $this->reader->requireObject($data, 'orderbook');

        return new OrderBookDto(
            symbol: $this->reader->requireString($data, 'symbol'),
            bids: $this->mapLevels($this->reader->requireObjectList($orderBook, 'bids')),
            asks: $this->mapLevels($this->reader->requireObjectList($orderBook, 'asks')),
        );
    }

    private function mapLevels(\MasyaSmv\FinamSdk\Collections\Transport\ApiPayloadCollection $levels): OrderBookLevelCollection
    {
        $items = [];

        foreach ($levels->payloads() as $level) {
            $items[] = new OrderBookLevelDto(
                price: $this->reader->requireDecimal($level, 'price'),
                quantity: $this->reader->requireDecimal($level, 'quantity'),
            );
        }

        /** @var list<OrderBookLevelDto> $items */
        return new OrderBookLevelCollection($items);
    }
}
