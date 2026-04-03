<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use DateTimeImmutable;
use MasyaSmv\FinamSdk\Collections\ExchangeCollection;
use MasyaSmv\FinamSdk\Collections\InstrumentCollection;
use MasyaSmv\FinamSdk\Collections\OperationCollection;
use MasyaSmv\FinamSdk\Collections\OrderBookRowCollection;
use MasyaSmv\FinamSdk\Collections\OrderCollection;
use MasyaSmv\FinamSdk\Collections\QuoteCollection;
use MasyaSmv\FinamSdk\Collections\ScheduleSessionCollection;
use MasyaSmv\FinamSdk\Collections\StringCollection;
use MasyaSmv\FinamSdk\Dto\Account\GetAccountRequest;
use MasyaSmv\FinamSdk\Dto\Account\OperationDto;
use MasyaSmv\FinamSdk\Dto\Account\OperationTradeDto;
use MasyaSmv\FinamSdk\Dto\Connect\TokenDetailsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\AllAssetsPageDto;
use MasyaSmv\FinamSdk\Dto\Instrument\AllAssetsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ClockDto;
use MasyaSmv\FinamSdk\Dto\Instrument\ExchangeDto;
use MasyaSmv\FinamSdk\Dto\Instrument\GetAssetParamsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\GetAssetRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\InstrumentDto;
use MasyaSmv\FinamSdk\Dto\Instrument\OptionsChainRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleDto;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleSessionDto;
use MasyaSmv\FinamSdk\Dto\Market\CandleDto;
use MasyaSmv\FinamSdk\Dto\Market\CandlesQueryDto;
use MasyaSmv\FinamSdk\Dto\Market\OrderBookDto;
use MasyaSmv\FinamSdk\Dto\Market\OrderBookRowDto;
use MasyaSmv\FinamSdk\Dto\Market\OrderbookRequest;
use MasyaSmv\FinamSdk\Dto\Market\QuoteDto;
use MasyaSmv\FinamSdk\Dto\Market\TradeDto;
use MasyaSmv\FinamSdk\Dto\Market\TradesRequest as MarketTradesRequest;
use MasyaSmv\FinamSdk\Dto\Order\CancelOrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrderDto;
use MasyaSmv\FinamSdk\Dto\Order\OrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrdersRequest;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderInputDto;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\PlaceSlTpOrderInputDto;
use MasyaSmv\FinamSdk\Dto\Order\PlaceSlTpOrderRequest;
use MasyaSmv\FinamSdk\Dto\Report\CreateAccountReportRequest;
use MasyaSmv\FinamSdk\Dto\Report\CreateAccountReportInputDto;
use MasyaSmv\FinamSdk\Dto\Report\GetAccountReportInfoRequest;
use MasyaSmv\FinamSdk\Dto\Report\ReportDateRangeDto;
use MasyaSmv\FinamSdk\Dto\Shared\Interval;
use MasyaSmv\FinamSdk\Dto\Shared\MoneyDto;
use MasyaSmv\FinamSdk\Dto\Transport\ApiDiagnosticContext;
use MasyaSmv\FinamSdk\Dto\Transport\ApiError;
use MasyaSmv\FinamSdk\Dto\Transport\ApiHeaders;
use MasyaSmv\FinamSdk\Dto\Transport\ApiMeta;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Dto\Transport\ApiRequestContext;
use MasyaSmv\FinamSdk\Exceptions\AccountResolutionException;
use MasyaSmv\FinamSdk\Exceptions\ApiRequestFailedException;
use MasyaSmv\FinamSdk\Exceptions\AuthException;
use MasyaSmv\FinamSdk\Exceptions\FinamSdkException;
use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;
use MasyaSmv\FinamSdk\Exceptions\InvalidResponseException;
use MasyaSmv\FinamSdk\Exceptions\ResponseMappingException;
use MasyaSmv\FinamSdk\Exceptions\TokenNotConfiguredException;

final class DtoCollectionCoverageTest extends TestCase
{
    /**
     * @param callable(): object $callback
     */
    private function assertInvalidRequest(callable $callback): void
    {
        try {
            $callback();
            self::fail('Expected InvalidRequestException was not thrown.');
        } catch (InvalidRequestException) {
            self::assertTrue(true);
        }
    }

    public function testTransportDtosAndExceptionsExposeAllState(): void
    {
        $payload = new ApiPayload(['message' => 'oops']);
        $headers = new ApiHeaders(['x-request-id' => ['req-1']]);
        $request = new ApiRequestContext('POST', 'orders', new ApiPayload(['foo' => 'bar']), new ApiPayload(['baz' => 'qux']), 2);
        $meta = new ApiMeta($headers, $request);
        $error = new ApiError('boom', 'server', $payload, 'raw');
        $context = new ApiDiagnosticContext('orders', $request, $headers, 'req-1', $payload, 'raw');

        $exception = new ApiRequestFailedException('failed', $context);
        $invalidResponse = new InvalidResponseException('invalid', 500, 'body', $context);

        $this->assertSame('boom', $error->message());
        $this->assertSame('server', $error->type());
        $this->assertSame($payload, $error->details());
        $this->assertSame('raw', $error->raw());
        $this->assertSame($headers, $meta->headers());
        $this->assertSame($request, $meta->request());
        $this->assertSame('POST', $request->method());
        $this->assertSame('orders', $request->uri());
        $this->assertSame(['foo' => 'bar'], $request->query()->toArray());
        $this->assertSame(['baz' => 'qux'], $request->payload()->toArray());
        $this->assertSame(2, $request->attempt());
        $this->assertSame([
            'method' => 'POST',
            'uri' => 'orders',
            'query' => ['foo' => 'bar'],
            'payload' => ['baz' => 'qux'],
            'attempt' => 2,
        ], $request->toArray());
        $this->assertSame('orders', $context->endpoint());
        $this->assertSame($request, $context->request());
        $this->assertSame($headers, $context->headers());
        $this->assertSame('req-1', $context->requestId());
        $this->assertSame($payload, $context->errorPayload());
        $this->assertSame('raw', $context->rawBody());
        $this->assertSame($context, $exception->context);
        $this->assertSame(500, $invalidResponse->httpStatus);
        $this->assertSame('body', $invalidResponse->rawBody);
        $this->assertSame($context, $invalidResponse->context);
        $this->assertInstanceOf(AuthException::class, new TokenNotConfiguredException('missing'));
        $this->assertInstanceOf(FinamSdkException::class, new AccountResolutionException('no account'));
        $this->assertInstanceOf(FinamSdkException::class, new ResponseMappingException('bad map'));
        $this->assertInstanceOf(\InvalidArgumentException::class, new InvalidRequestException('bad request'));
    }

    public function testAccountAndSharedDtosAndCollectionsExposeGetters(): void
    {
        $when = new DateTimeImmutable('2026-04-01T10:00:00Z');
        $money = new MoneyDto('RUB', '123', 450000000);
        $trade = new OperationTradeDto('trade-1', 'order-1');
        $operation = new OperationDto('op-1', 'acc-1', 'cash', 'txn', 'deposit', 'SBER@MISX', $when, $money, '10', $trade);
        $collection = new OperationCollection([$operation]);

        $this->assertSame('RUB', $money->currencyCode());
        $this->assertSame('123', $money->units());
        $this->assertSame(450000000, $money->nanos());
        $this->assertSame('trade-1', $trade->tradeId());
        $this->assertSame('order-1', $trade->orderId());
        $this->assertSame('op-1', $operation->id());
        $this->assertSame('acc-1', $operation->accountId());
        $this->assertSame('cash', $operation->category());
        $this->assertSame('txn', $operation->transactionCategory());
        $this->assertSame('deposit', $operation->transactionName());
        $this->assertSame('SBER@MISX', $operation->symbol());
        $this->assertSame($when, $operation->occurredAt());
        $this->assertSame($money, $operation->change());
        $this->assertSame('10', $operation->changeQuantity());
        $this->assertSame($trade, $operation->trade());
        $this->assertSame($operation, $collection->findById('op-1'));
        $this->assertNull($collection->findById('missing'));
        $this->assertCount(1, $collection->between(new DateTimeImmutable('2026-04-01T09:00:00Z'), new DateTimeImmutable('2026-04-01T11:00:00Z')));
        $this->assertCount(0, $collection->between(new DateTimeImmutable('2026-04-01T11:00:01Z'), new DateTimeImmutable('2026-04-01T12:00:00Z')));
    }

    public function testInstrumentDtosRequestsAndCollectionsExposeGetters(): void
    {
        $instrument = new InstrumentDto('SBER@MISX', '1', 'SBER', 'MISX', 'stock', 'Sberbank', 'TQBR', 2, '0.01', 'RUB', '2026-12-31', '10', 'RU0009029540');
        $instruments = new InstrumentCollection([$instrument]);
        $exchange = new ExchangeDto('MISX', 'Moscow Exchange');
        $exchanges = new ExchangeCollection([$exchange]);
        $sessions = new ScheduleSessionCollection([
            new ScheduleSessionDto('CORE', new DateTimeImmutable('2026-04-01T10:00:00Z'), new DateTimeImmutable('2026-04-01T12:00:00Z')),
        ]);
        $schedule = new ScheduleDto('SBER@MISX', $sessions);
        $page = new AllAssetsPageDto($instruments, 15);

        $this->assertSame('SBER@MISX', (new GetAssetRequest('SBER@MISX', 'ACC-1'))->symbol());
        $this->assertSame(['account_id' => 'ACC-1'], (new GetAssetRequest('SBER@MISX', 'ACC-1'))->toQuery());
        $this->assertSame('SBER@MISX', (new GetAssetParamsRequest('SBER@MISX', 'ACC-1'))->symbol());
        $this->assertSame(['account_id' => 'ACC-1'], (new GetAssetParamsRequest('SBER@MISX', 'ACC-1'))->toQuery());
        $this->assertSame('base-1', (new TokenDetailsRequest('base-1'))->token());
        $this->assertNull((new TokenDetailsRequest())->token());
        $this->assertSame(15, $page->nextCursor());
        $this->assertTrue($page->hasNextPage());
        $this->assertFalse((new AllAssetsPageDto($instruments, null))->hasNextPage());
        $this->assertSame($instruments, $page->assets());
        $this->assertSame('SBER@MISX', $instrument->symbol());
        $this->assertSame('1', $instrument->id());
        $this->assertSame('SBER', $instrument->ticker());
        $this->assertSame('MISX', $instrument->mic());
        $this->assertSame('stock', $instrument->type());
        $this->assertSame('Sberbank', $instrument->name());
        $this->assertSame('TQBR', $instrument->board());
        $this->assertSame(2, $instrument->decimals());
        $this->assertSame('0.01', $instrument->minStep());
        $this->assertSame('RUB', $instrument->quoteCurrency());
        $this->assertSame('2026-12-31', $instrument->expirationDate());
        $this->assertSame('Sberbank', $instrument->shortName());
        $this->assertSame('Sberbank', $instrument->description());
        $this->assertSame('MISX', $instrument->market());
        $this->assertSame('RUB', $instrument->currency());
        $this->assertSame('10', $instrument->lotSize());
        $this->assertSame('RU0009029540', $instrument->isin());
        $this->assertSame($instrument, $instruments->findBySymbol('SBER@MISX'));
        $this->assertNull($instruments->findBySymbol('GAZP@MISX'));
        $this->assertSame('MISX', $exchange->mic());
        $this->assertSame('Moscow Exchange', $exchange->name());
        $this->assertSame($exchange, $exchanges->findByMic('MISX'));
        $this->assertNull($exchanges->findByMic('SPBX'));
        $this->assertSame('SBER@MISX', $schedule->symbol());
        $this->assertSame($sessions, $schedule->sessions());
        $this->assertSame('CORE', $sessions->firstByType('CORE')?->type());
        $this->assertNull($sessions->firstByType('OTHER'));
        $this->assertSame('SBER@MISX', (new ScheduleRequest('SBER@MISX'))->symbol());
        $this->assertSame([], (new ScheduleRequest('SBER@MISX'))->toQuery());
        $this->assertSame(['cursor' => 1, 'only_disabled' => true], (new AllAssetsRequest(1, false, true))->toQuery());
        $this->assertSame(['root' => 'RI', 'expiration_date' => '2026-06-18'], (new OptionsChainRequest('RI@SPBX', 'RI', '2026-06-18'))->toQuery());
        $this->assertSame([], (new OptionsChainRequest('RI@SPBX'))->toQuery());
        $clockTimestamp = new DateTimeImmutable('2026-04-01T00:00:00Z');
        $this->assertEquals($clockTimestamp, (new ClockDto($clockTimestamp))->timestamp());
    }

    public function testMarketOrderAndReportDtosExposeGettersAndPayloads(): void
    {
        $time = new DateTimeImmutable('2026-04-01T10:00:00Z');
        $candle = new CandleDto($time, '100', '110', '90', '105', '1000');
        $row = new OrderBookRowDto('100', '5', '0', 'SELL', 'MPID', $time);
        $book = new OrderBookDto('SBER@MISX', new OrderBookRowCollection([
            $row,
            new OrderBookRowDto('99', '0', '4', 'BUY', null, null),
        ]));
        $quote = new QuoteDto('SBER@MISX', '100', '+1', '+1%', $time);
        $trade = new TradeDto('SBER@MISX', '100', '3', $time, 'BUY');
        $order = new OrderDto('ORD-1', 'EXEC-1', 'NEW', 'ACC-1', 'SBER@MISX', '1', 'BUY', 'LIMIT', 'DAY', 'CID', 'note', '100', '99', $time, $time, $time, '10', '5', '5');
        $orders = new OrderCollection([$order]);
        $quotes = new QuoteCollection([$quote]);
        $reportRange = new ReportDateRangeDto(new DateTimeImmutable('2026-03-01'), new DateTimeImmutable('2026-03-31'));
        $reportInput = new CreateAccountReportInputDto('ACC-1', 'REPORT_FORM_SAMPLE', $reportRange);
        $reportRequest = new CreateAccountReportRequest($reportInput);
        $slTpInput = new PlaceSlTpOrderInputDto('SBER@MISX', 'SELL', '1', '100');
        $slTpRequest = new PlaceSlTpOrderRequest('ACC-1', $slTpInput);

        $this->assertSame($time, $candle->timestamp());
        $this->assertSame('100', $candle->open());
        $this->assertSame('110', $candle->high());
        $this->assertSame('90', $candle->low());
        $this->assertSame('105', $candle->close());
        $this->assertSame('1000', $candle->volume());
        $this->assertSame('100', $row->price());
        $this->assertSame('5', $row->sellSize());
        $this->assertSame('0', $row->buySize());
        $this->assertSame('SELL', $row->action());
        $this->assertSame('MPID', $row->mpid());
        $this->assertSame($time, $row->timestamp());
        $this->assertSame('SBER@MISX', $book->symbol());
        $this->assertCount(1, $book->sellRows());
        $this->assertCount(1, $book->buyRows());
        $this->assertSame('SBER@MISX', $quote->symbol());
        $this->assertSame('100', $quote->price());
        $this->assertSame('+1', $quote->change());
        $this->assertSame('+1%', $quote->percentChange());
        $this->assertSame($time, $quote->timestamp());
        $this->assertSame('SBER@MISX', $trade->symbol());
        $this->assertSame('100', $trade->price());
        $this->assertSame('3', $trade->size());
        $this->assertSame($time, $trade->timestamp());
        $this->assertSame('BUY', $trade->side());
        $this->assertSame($quote, $quotes->findBySymbol('SBER@MISX'));
        $this->assertNull($quotes->findBySymbol('GAZP@MISX'));
        $this->assertSame('ORD-1', $order->orderId());
        $this->assertSame('EXEC-1', $order->execId());
        $this->assertSame('NEW', $order->status());
        $this->assertSame('ACC-1', $order->accountId());
        $this->assertSame('SBER@MISX', $order->symbol());
        $this->assertSame('1', $order->quantity());
        $this->assertSame('BUY', $order->side());
        $this->assertSame('LIMIT', $order->type());
        $this->assertSame('DAY', $order->timeInForce());
        $this->assertSame('CID', $order->clientOrderId());
        $this->assertSame('note', $order->comment());
        $this->assertSame('100', $order->limitPrice());
        $this->assertSame('99', $order->stopPrice());
        $this->assertSame($time, $order->transactAt());
        $this->assertSame($time, $order->acceptAt());
        $this->assertSame($time, $order->withdrawAt());
        $this->assertSame('10', $order->initialQuantity());
        $this->assertSame('5', $order->executedQuantity());
        $this->assertSame('5', $order->remainingQuantity());
        $this->assertSame($order, $orders->findById('ORD-1'));
        $this->assertNull($orders->findById('ORD-2'));
        $this->assertEquals(new DateTimeImmutable('2026-03-01'), DateTimeImmutable::createFromInterface($reportRange->from()));
        $this->assertEquals(new DateTimeImmutable('2026-03-31'), DateTimeImmutable::createFromInterface($reportRange->to()));
        $this->assertSame(['from' => '2026-03-01', 'to' => '2026-03-31'], $reportRange->toPayload());
        $this->assertSame('ACC-1', $reportInput->accountId());
        $this->assertSame('REPORT_FORM_SAMPLE', $reportInput->reportForm());
        $this->assertSame($reportRange, $reportInput->dateRange());
        $this->assertSame($reportInput, $reportRequest->payload());
        $this->assertSame($slTpInput, $slTpRequest->payload());
        $this->assertSame('report-1', (new \MasyaSmv\FinamSdk\Dto\Report\CreatedAccountReportDto('report-1'))->reportId());
        $this->assertSame('report-1', (new GetAccountReportInfoRequest('report-1'))->reportId());
        $this->assertSame('ACC-1', (new PlaceOrderRequest('ACC-1', new PlaceOrderInputDto('SBER@MISX', '1', 'BUY', 'LIMIT', 'DAY')))->accountId());
        $this->assertSame('ACC-1', (new PlaceSlTpOrderRequest('ACC-1', new PlaceSlTpOrderInputDto('SBER@MISX', 'SELL', '1', '100')))->accountId());
        $this->assertSame('SBER@MISX', (new OrderbookRequest('SBER@MISX'))->symbol());
        $this->assertSame([], (new OrderbookRequest('SBER@MISX'))->toQuery());
        $this->assertSame('SBER@MISX', (new MarketTradesRequest('SBER@MISX'))->symbol());
        $this->assertSame([], (new MarketTradesRequest('SBER@MISX'))->toQuery());
    }

    public function testRequestDtosValidateAndSerializeEdgeCases(): void
    {
        $this->assertSame('ACC-1', (new GetAccountRequest('ACC-1'))->accountId());
        $this->assertSame([], (new OrdersRequest('ACC-1'))->toQuery());
        $this->assertSame('ACC-1', (new OrdersRequest('ACC-1'))->accountId());
        $this->assertSame('ORD-1', (new OrderRequest('ACC-1', 'ORD-1'))->orderId());
        $this->assertSame('ACC-1', (new CancelOrderRequest('ACC-1', 'ORD-1'))->accountId());
        $this->assertSame('ORD-1', (new CancelOrderRequest('ACC-1', 'ORD-1'))->orderId());
        $this->assertSame([], (new CancelOrderRequest('ACC-1', 'ORD-1'))->toPayload());
        $this->assertSame([
            'symbol' => 'SBER@MISX',
            'quantity' => '1',
            'side' => 'BUY',
            'type' => 'LIMIT',
            'time_in_force' => 'DAY',
            'limit_price' => '100',
            'stop_price' => '99',
            'stop_condition' => 'LAST_UP',
            'client_order_id' => 'CID',
            'comment' => 'comment',
        ], (new PlaceOrderInputDto('SBER@MISX', '1', 'BUY', 'LIMIT', 'DAY', '100', '99', 'LAST_UP', 'CID', 'comment'))->toPayload());
        $this->assertSame([
            'symbol' => 'SBER@MISX',
            'side' => 'SELL',
            'quantity_sl' => ['value' => '1'],
            'sl_price' => ['value' => '100'],
            'limit_price' => ['value' => '99'],
            'quantity_tp' => ['value' => '2'],
            'tp_price' => ['value' => '110'],
            'tp_guard_spread' => ['value' => '0.5'],
            'comment' => 'comment',
        ], (new PlaceSlTpOrderInputDto('SBER@MISX', 'SELL', '1', '100', '99', '2', '110', '0.5', 'comment'))->toPayload());
        $this->assertSame(['start' => 1, 'end' => 2], (new Interval(1, 2))->toArray());
        $this->assertSame(['interval.startTime' => '1970-01-01T00:00:01Z', 'interval.endTime' => '1970-01-01T00:00:02Z'], (new Interval(1, 2))->toRestQuery());
        $candles = new CandlesQueryDto(
            'SBER@MISX',
            'TIME_FRAME_M1',
            new DateTimeImmutable('1970-01-01T00:00:01Z'),
            new DateTimeImmutable('1970-01-01T00:00:02Z'),
        );
        $this->assertSame([
            'symbol' => 'SBER@MISX',
            'timeframe' => 'TIME_FRAME_M1',
            'interval.startTime' => '1970-01-01T00:00:01Z',
            'interval.endTime' => '1970-01-01T00:00:02Z',
        ], $candles->toQuery());
        $this->assertInvalidRequest(fn () => new GetAccountRequest(''));
        $this->assertInvalidRequest(fn () => new OrdersRequest(''));
        $this->assertInvalidRequest(fn () => new OrderRequest('', 'ORD-1'));
        $this->assertInvalidRequest(fn () => new OrderRequest('ACC-1', ''));
        $this->assertInvalidRequest(fn () => new CancelOrderRequest('', 'ORD-1'));
        $this->assertInvalidRequest(fn () => new CancelOrderRequest('ACC-1', ''));
        $this->assertInvalidRequest(fn () => new PlaceOrderRequest('', new PlaceOrderInputDto('SBER@MISX', '1', 'BUY', 'LIMIT', 'DAY')));
        $this->assertInvalidRequest(fn () => new PlaceSlTpOrderRequest('', new PlaceSlTpOrderInputDto('SBER@MISX', 'SELL', '1', '100')));
        $this->assertInvalidRequest(fn () => new PlaceOrderInputDto('', '1', 'BUY', 'LIMIT', 'DAY'));
        $this->assertInvalidRequest(fn () => new PlaceOrderInputDto('SBER@MISX', '', 'BUY', 'LIMIT', 'DAY'));
        $this->assertInvalidRequest(fn () => new PlaceOrderInputDto('SBER@MISX', '1', '', 'LIMIT', 'DAY'));
        $this->assertInvalidRequest(fn () => new PlaceOrderInputDto('SBER@MISX', '1', 'BUY', '', 'DAY'));
        $this->assertInvalidRequest(fn () => new PlaceOrderInputDto('SBER@MISX', '1', 'BUY', 'LIMIT', ''));
        $this->assertInvalidRequest(fn () => new PlaceSlTpOrderInputDto('', 'SELL', '1', '100'));
        $this->assertInvalidRequest(fn () => new PlaceSlTpOrderInputDto('SBER@MISX', '', '1', '100'));
        $this->assertInvalidRequest(fn () => new PlaceSlTpOrderInputDto('SBER@MISX', 'SELL'));
        $this->assertInvalidRequest(fn () => new PlaceSlTpOrderInputDto('SBER@MISX', 'SELL', '1', null));
        $this->assertInvalidRequest(fn () => new PlaceSlTpOrderInputDto('SBER@MISX', 'SELL', null, null, null, '1', null));
        $this->assertInvalidRequest(fn () => new ReportDateRangeDto(new DateTimeImmutable('2026-04-02'), new DateTimeImmutable('2026-04-01')));
        $this->assertInvalidRequest(fn () => new CreateAccountReportInputDto('', 'FORM', new ReportDateRangeDto(new DateTimeImmutable('2026-04-01'), new DateTimeImmutable('2026-04-02'))));
        $this->assertInvalidRequest(fn () => new CreateAccountReportInputDto('ACC-1', '', new ReportDateRangeDto(new DateTimeImmutable('2026-04-01'), new DateTimeImmutable('2026-04-02'))));
        $this->assertInvalidRequest(fn () => new GetAccountReportInfoRequest(''));
        $this->assertInvalidRequest(fn () => new GetAssetRequest('', 'ACC-1'));
        $this->assertInvalidRequest(fn () => new GetAssetRequest('SBER@MISX', ''));
        $this->assertInvalidRequest(fn () => new GetAssetParamsRequest('', 'ACC-1'));
        $this->assertInvalidRequest(fn () => new GetAssetParamsRequest('SBER@MISX', ''));
        $this->assertInvalidRequest(fn () => new ScheduleRequest(''));
        $this->assertInvalidRequest(fn () => new OptionsChainRequest(''));
        $this->assertInvalidRequest(fn () => new AllAssetsRequest(-1));
        $this->assertInvalidRequest(fn () => new AllAssetsRequest(1, true, true));
        $this->assertInvalidRequest(fn () => new \MasyaSmv\FinamSdk\Dto\Market\QuotesRequest(''));
        $this->assertInvalidRequest(fn () => new \MasyaSmv\FinamSdk\Dto\Market\OrderbookRequest(''));
        $this->assertInvalidRequest(fn () => new \MasyaSmv\FinamSdk\Dto\Market\TradesRequest(''));
        $this->assertInvalidRequest(fn () => new \MasyaSmv\FinamSdk\Dto\Market\CandlesQueryDto(
            '',
            'TIME_FRAME_M1',
            new DateTimeImmutable('2026-04-01'),
            new DateTimeImmutable('2026-04-02'),
        ));
        $this->assertInvalidRequest(fn () => new \MasyaSmv\FinamSdk\Dto\Market\CandlesQueryDto(
            'SBER@MISX',
            '',
            new DateTimeImmutable('2026-04-01'),
            new DateTimeImmutable('2026-04-02'),
        ));
        $this->assertInvalidRequest(fn () => new \MasyaSmv\FinamSdk\Dto\Market\CandlesQueryDto(
            'SBER@MISX',
            'TIME_FRAME_M1',
            new DateTimeImmutable('2026-04-02'),
            new DateTimeImmutable('2026-04-01'),
        ));
        $this->assertInvalidRequest(fn () => new \MasyaSmv\FinamSdk\Dto\Account\TradesRequest('', 1));
        $this->assertInvalidRequest(fn () => new \MasyaSmv\FinamSdk\Dto\Account\TradesRequest('ACC-1', 0));
        $this->assertInvalidRequest(fn () => new \MasyaSmv\FinamSdk\Dto\Account\TransactionsRequest('', 1));
        $this->assertInvalidRequest(fn () => new \MasyaSmv\FinamSdk\Dto\Account\TransactionsRequest('ACC-1', 0));
        $this->assertInvalidRequest(fn () => new Interval(0, 1));
        $this->assertInvalidRequest(fn () => new Interval(2, 1));
    }
}
