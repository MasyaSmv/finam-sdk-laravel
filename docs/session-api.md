# High-level session API

High-level API начинается с:

```php
use MasyaSmv\FinamSdk\Facades\Finam;

$session = Finam::connect($token);
```

Ниже собраны основные методы.

## Сессия

### `sessionDetails(): SessionDetailsDto`

Возвращает информацию о текущей сессии:

- время создания
- время истечения
- список account ids
- список market data permissions
- read-only флаг

Пример:

```php
$details = $session->sessionDetails();

$accountIds = $details->accountIds();
$readonly = $details->readonly();
```

## Операции

### `getOperationsByDate(DateTimeInterface $startDate, DateTimeInterface $endDate, ?string $accountId = null, ?int $limit = null): OperationCollection`

Пример:

```php
use DateTimeImmutable;

$operations = $session->getOperationsByDate(
    new DateTimeImmutable('2026-04-01'),
    new DateTimeImmutable('2026-04-03'),
    '1930918',
);
```

## Заявки

### `getOrders(?string $accountId = null): OrderCollection`

```php
$orders = $session->getOrders('1930918');
$first = $orders->first();
```

### `getOrder(string $orderId, ?string $accountId = null): OrderDto`

```php
$order = $session->getOrder('123456789', '1930918');
```

### `placeOrder(PlaceOrderInputDto $order, ?string $accountId = null): OrderDto`

```php
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderInputDto;

$order = $session->placeOrder(
    new PlaceOrderInputDto(
        symbol: 'SBER@MISX',
        quantity: '1',
        side: 'BUY',
        type: 'LIMIT',
        timeInForce: 'TIME_IN_FORCE_DAY',
        limitPrice: '250.00',
    ),
    '1930918',
);
```

### `placeSlTpOrder(PlaceSlTpOrderInputDto $order, ?string $accountId = null): OrderDto`

```php
use MasyaSmv\FinamSdk\Dto\Order\PlaceSlTpOrderInputDto;

$order = $session->placeSlTpOrder(
    new PlaceSlTpOrderInputDto(
        symbol: 'SBER@MISX',
        side: 'SELL',
        quantitySl: '1',
        slPrice: '240.00',
        quantityTp: '1',
        tpPrice: '270.00',
    ),
    '1930918',
);
```

## Инструменты

### `getAllInstruments(?int $cursor = null, bool $onlyActive = false, bool $onlyDisabled = false): AllAssetsPageDto`

Возвращает одну страницу результатов.

```php
$page = $session->getAllInstruments();
$items = $page->assets();
$nextCursor = $page->nextCursor();
```

### `getInstruments(): InstrumentCollection`

Возвращает все инструменты через внутренний проход по страницам.

```php
$instruments = $session->getInstruments();
$sber = $instruments->findBySymbol('SBER@MISX');
```

### `getInstrument(string $symbol, ?string $accountId = null): InstrumentDto`

```php
$instrument = $session->getInstrument('SBER@MISX', '1930918');
```

### `getExchanges(): ExchangeCollection`

```php
$exchanges = $session->getExchanges();
```

### `getClock(): ClockDto`

```php
$clock = $session->getClock();
```

### `getSchedule(string $symbol): ScheduleDto`

```php
$schedule = $session->getSchedule('SBER@MISX');
```

## Market data

### `getLatestQuotes(array $symbols): QuoteCollection`

```php
$quotes = $session->getLatestQuotes(['SBER@MISX', 'GAZP@MISX']);
```

### `getCandles(CandlesQueryDto $query): CandleCollection`

```php
use DateTimeImmutable;
use MasyaSmv\FinamSdk\Dto\Market\CandlesQueryDto;

$candles = $session->getCandles(new CandlesQueryDto(
    symbol: 'SBER@MISX',
    timeframe: 'M1',
    startDate: new DateTimeImmutable('-1 hour'),
    endDate: new DateTimeImmutable('now'),
));
```

### `getOrderBook(string $symbol): OrderBookDto`

```php
$book = $session->getOrderBook('SBER@MISX');
$rows = $book->rows();
```

### `getLatestTrades(string $symbol): TradeCollection`

```php
$trades = $session->getLatestTrades('SBER@MISX');
```

## Usage metrics

### `getUsageMetrics(): UsageMetricsDto`

```php
$usage = $session->getUsageMetrics();

foreach ($usage->quotas() as $quota) {
    echo $quota->name() . ': ' . $quota->remaining() . PHP_EOL;
}
```

`resetTime()` у quota может быть `null`. Это нормальный live-сценарий.

## Отчёты

### `createAccountReport(CreateAccountReportInputDto $report): CreatedAccountReportDto`

```php
use DateTimeImmutable;
use MasyaSmv\FinamSdk\Dto\Report\CreateAccountReportInputDto;
use MasyaSmv\FinamSdk\Dto\Report\ReportDateRangeDto;

$created = $session->createAccountReport(new CreateAccountReportInputDto(
    accountId: '1930918',
    reportForm: 'REPORT_FORM_PDF',
    dateRange: new ReportDateRangeDto(
        from: new DateTimeImmutable('2026-04-01'),
        to: new DateTimeImmutable('2026-04-03'),
    ),
));
```

### `getAccountReportInfo(string $reportId): AccountReportInfoDto`

```php
$info = $session->getAccountReportInfo($created->reportId());
$details = $info->details();
```

Важно:

- отчёты поддерживаются в SDK
- но конкретные допустимые значения `reportForm` зависят от актуальной документации Finam и прав твоего аккаунта
- если сервер вернул валидационную ошибку, это не обязательно ошибка пакета
