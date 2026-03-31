<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts;

use DateTimeInterface;
use MasyaSmv\FinamSdk\Collections\CandleCollection;
use MasyaSmv\FinamSdk\Collections\ExchangeCollection;
use MasyaSmv\FinamSdk\Collections\InstrumentCollection;
use MasyaSmv\FinamSdk\Collections\OperationCollection;
use MasyaSmv\FinamSdk\Collections\OrderCollection;
use MasyaSmv\FinamSdk\Collections\QuoteCollection;
use MasyaSmv\FinamSdk\Collections\TradeCollection;
use MasyaSmv\FinamSdk\Dto\Connect\SessionDetailsDto;
use MasyaSmv\FinamSdk\Dto\Instrument\AllAssetsPageDto;
use MasyaSmv\FinamSdk\Dto\Instrument\ClockDto;
use MasyaSmv\FinamSdk\Dto\Instrument\InstrumentDto;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleDto;
use MasyaSmv\FinamSdk\Dto\Market\CandlesQueryDto;
use MasyaSmv\FinamSdk\Dto\Market\OrderBookDto;
use MasyaSmv\FinamSdk\Dto\Order\OrderDto;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderInputDto;
use MasyaSmv\FinamSdk\Dto\Order\PlaceSlTpOrderInputDto;
use MasyaSmv\FinamSdk\Dto\Report\AccountReportInfoDto;
use MasyaSmv\FinamSdk\Dto\Report\CreateAccountReportInputDto;
use MasyaSmv\FinamSdk\Dto\Report\CreatedAccountReportDto;
use MasyaSmv\FinamSdk\Dto\UsageMetrics\UsageMetricsDto;

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

    public function placeSlTpOrder(PlaceSlTpOrderInputDto $order, ?string $accountId = null): OrderDto;

    public function getAllInstruments(?int $cursor = null, bool $onlyActive = false, bool $onlyDisabled = false): AllAssetsPageDto;

    public function getInstruments(): InstrumentCollection;

    public function getInstrument(string $symbol, ?string $accountId = null): InstrumentDto;

    public function getExchanges(): ExchangeCollection;

    public function getClock(): ClockDto;

    public function getSchedule(string $symbol): ScheduleDto;

    /**
     * @param list<string> $symbols
     */
    public function getLatestQuotes(array $symbols): QuoteCollection;

    public function getCandles(CandlesQueryDto $query): CandleCollection;

    public function getOrderBook(string $symbol): OrderBookDto;

    public function getLatestTrades(string $symbol): TradeCollection;

    public function getUsageMetrics(): UsageMetricsDto;

    public function createAccountReport(CreateAccountReportInputDto $report): CreatedAccountReportDto;

    public function getAccountReportInfo(string $reportId): AccountReportInfoDto;
}
