<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Dto\Market\OrderBookDto;
use MasyaSmv\FinamSdk\Collections\OrderBookRowCollection;
use MasyaSmv\FinamSdk\Dto\Market\OrderBookRowDto;
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
            rows: $this->mapRows($this->reader->requireObjectList($orderBook, 'rows')),
        );
    }

    private function mapRows(\MasyaSmv\FinamSdk\Collections\Transport\ApiPayloadCollection $rows): OrderBookRowCollection
    {
        $items = [];

        foreach ($rows->payloads() as $row) {
            $items[] = new OrderBookRowDto(
                price: $this->reader->requireDecimal($row, 'price'),
                sellSize: $this->reader->requireDecimal($row, 'sell_size'),
                buySize: $this->reader->requireDecimal($row, 'buy_size'),
                action: $this->reader->optionalString($row, 'action'),
                mpid: $this->reader->optionalString($row, 'mpid'),
                timestamp: $this->reader->optionalDateTime($row, 'timestamp'),
            );
        }

        /** @var list<OrderBookRowDto> $items */
        return new OrderBookRowCollection($items);
    }
}
