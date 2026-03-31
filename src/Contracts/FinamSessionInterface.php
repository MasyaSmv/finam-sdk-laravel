<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts;

use DateTimeInterface;
use MasyaSmv\FinamSdk\Collections\CandleCollection;
use MasyaSmv\FinamSdk\Collections\InstrumentCollection;
use MasyaSmv\FinamSdk\Collections\OperationCollection;
use MasyaSmv\FinamSdk\Collections\OrderCollection;
use MasyaSmv\FinamSdk\Collections\QuoteCollection;
use MasyaSmv\FinamSdk\Dto\Connect\SessionDetailsDto;
use MasyaSmv\FinamSdk\Dto\Instrument\InstrumentDto;
use MasyaSmv\FinamSdk\Dto\Market\CandlesQueryDto;
use MasyaSmv\FinamSdk\Dto\Order\OrderDto;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderInputDto;

interface FinamSessionInterface
{
    public function sessionDetails(): SessionDetailsDto;

    public function getOperationsByDate(
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
        ?string $accountId = null,
        ?int $limit = null,
    ): OperationCollection;

    public function getOrders(?string $accountId = null): OrderCollection;

    public function getOrder(string $orderId, ?string $accountId = null): OrderDto;

    public function placeOrder(PlaceOrderInputDto $order, ?string $accountId = null): OrderDto;

    public function getInstruments(): InstrumentCollection;

    public function getInstrument(string $symbol, ?string $accountId = null): InstrumentDto;

    /**
     * @param list<string> $symbols
     */
    public function getLatestQuotes(array $symbols): QuoteCollection;

    public function getCandles(CandlesQueryDto $query): CandleCollection;
}
