<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session;

use DateTimeInterface;
use MasyaSmv\FinamSdk\Contracts\Api\AccountApiInterface;
use MasyaSmv\FinamSdk\Contracts\Api\ConnectApiInterface;
use MasyaSmv\FinamSdk\Contracts\Api\InstrumentApiInterface;
use MasyaSmv\FinamSdk\Contracts\Api\MarketApiInterface;
use MasyaSmv\FinamSdk\Contracts\Api\OrderApiInterface;
use MasyaSmv\FinamSdk\Contracts\FinamSessionInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionDetailsServiceInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionInstrumentServiceInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionMarketDataServiceInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionOperationServiceInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionOrderServiceInterface;
use MasyaSmv\FinamSdk\Dto\Connect\SessionDetailsDto;
use MasyaSmv\FinamSdk\Dto\Market\CandlesQueryDto;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderInputDto;
use MasyaSmv\FinamSdk\Session\Mapper\CandleMapper;
use MasyaSmv\FinamSdk\Session\Mapper\ClockMapper;
use MasyaSmv\FinamSdk\Session\Mapper\ExchangeMapper;
use MasyaSmv\FinamSdk\Session\Mapper\InstrumentMapper;
use MasyaSmv\FinamSdk\Session\Mapper\OperationMapper;
use MasyaSmv\FinamSdk\Session\Mapper\OrderBookMapper;
use MasyaSmv\FinamSdk\Session\Mapper\OrderMapper;
use MasyaSmv\FinamSdk\Session\Mapper\QuoteMapper;
use MasyaSmv\FinamSdk\Session\Mapper\ScheduleMapper;
use MasyaSmv\FinamSdk\Session\Mapper\SessionDetailsMapper;
use MasyaSmv\FinamSdk\Session\Mapper\TradeMapper;
use MasyaSmv\FinamSdk\Session\Service\SessionAccountResolver;
use MasyaSmv\FinamSdk\Session\Service\SessionDetailsService;
use MasyaSmv\FinamSdk\Session\Service\SessionInstrumentService;
use MasyaSmv\FinamSdk\Session\Service\SessionMarketDataService;
use MasyaSmv\FinamSdk\Session\Service\SessionOperationService;
use MasyaSmv\FinamSdk\Session\Service\SessionOrderService;
use MasyaSmv\FinamSdk\Session\Support\ApiResponseDecoder;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

final class FinamSession implements FinamSessionInterface
{
    public function __construct(
        private SessionDetailsServiceInterface $detailsService,
        private SessionOperationServiceInterface $operationService,
        private SessionOrderServiceInterface $orderService,
        private SessionInstrumentServiceInterface $instrumentService,
        private SessionMarketDataServiceInterface $marketDataService,
    ) {
    }

    public static function fromApis(
        ConnectApiInterface $connectApi,
        AccountApiInterface $accountApi,
        OrderApiInterface $orderApi,
        InstrumentApiInterface $instrumentApi,
        MarketApiInterface $marketApi,
    ): self {
        $reader = new ApiValueReader();
        $decoder = new ApiResponseDecoder($reader);
        $detailsService = new SessionDetailsService(
            connectApi: $connectApi,
            decoder: $decoder,
            mapper: new SessionDetailsMapper($reader),
        );
        $accountResolver = new SessionAccountResolver($detailsService);

        return new self(
            detailsService: $detailsService,
            operationService: new SessionOperationService(
                accountApi: $accountApi,
                accountResolver: $accountResolver,
                decoder: $decoder,
                mapper: new OperationMapper($reader),
            ),
            orderService: new SessionOrderService(
                orderApi: $orderApi,
                accountResolver: $accountResolver,
                decoder: $decoder,
                mapper: new OrderMapper($reader),
            ),
            instrumentService: new SessionInstrumentService(
                instrumentApi: $instrumentApi,
                decoder: $decoder,
                mapper: new InstrumentMapper($reader),
                exchangeMapper: new ExchangeMapper($reader),
                clockMapper: new ClockMapper($reader),
                scheduleMapper: new ScheduleMapper($reader),
            ),
            marketDataService: new SessionMarketDataService(
                marketApi: $marketApi,
                decoder: $decoder,
                quoteMapper: new QuoteMapper($reader),
                candleMapper: new CandleMapper($reader),
                orderBookMapper: new OrderBookMapper($reader),
                tradeMapper: new TradeMapper($reader),
            ),
        );
    }

    public function sessionDetails(): SessionDetailsDto
    {
        return $this->detailsService->sessionDetails();
    }

    public function getOperationsByDate(
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
        ?string $accountId = null,
        ?int $limit = null,
    ): \MasyaSmv\FinamSdk\Collections\OperationCollection {
        return $this->operationService->getOperationsByDate($startDate, $endDate, $accountId, $limit);
    }

    public function getOrders(?string $accountId = null): \MasyaSmv\FinamSdk\Collections\OrderCollection
    {
        return $this->orderService->getOrders($accountId);
    }

    public function getOrder(string $orderId, ?string $accountId = null): \MasyaSmv\FinamSdk\Dto\Order\OrderDto
    {
        return $this->orderService->getOrder($orderId, $accountId);
    }

    public function placeOrder(PlaceOrderInputDto $order, ?string $accountId = null): \MasyaSmv\FinamSdk\Dto\Order\OrderDto
    {
        return $this->orderService->placeOrder($order, $accountId);
    }

    public function getInstruments(): \MasyaSmv\FinamSdk\Collections\InstrumentCollection
    {
        return $this->instrumentService->getInstruments();
    }

    public function getInstrument(string $symbol, ?string $accountId = null): \MasyaSmv\FinamSdk\Dto\Instrument\InstrumentDto
    {
        return $this->instrumentService->getInstrument($symbol, $accountId);
    }

    public function getExchanges(): \MasyaSmv\FinamSdk\Collections\ExchangeCollection
    {
        return $this->instrumentService->getExchanges();
    }

    public function getClock(): \MasyaSmv\FinamSdk\Dto\Instrument\ClockDto
    {
        return $this->instrumentService->getClock();
    }

    public function getSchedule(string $symbol): \MasyaSmv\FinamSdk\Dto\Instrument\ScheduleDto
    {
        return $this->instrumentService->getSchedule($symbol);
    }

    public function getLatestQuotes(array $symbols): \MasyaSmv\FinamSdk\Collections\QuoteCollection
    {
        return $this->marketDataService->getLatestQuotes($symbols);
    }

    public function getCandles(CandlesQueryDto $query): \MasyaSmv\FinamSdk\Collections\CandleCollection
    {
        return $this->marketDataService->getCandles($query);
    }

    public function getOrderBook(string $symbol): \MasyaSmv\FinamSdk\Dto\Market\OrderBookDto
    {
        return $this->marketDataService->getOrderBook($symbol);
    }

    public function getLatestTrades(string $symbol): \MasyaSmv\FinamSdk\Collections\TradeCollection
    {
        return $this->marketDataService->getLatestTrades($symbol);
    }
}
