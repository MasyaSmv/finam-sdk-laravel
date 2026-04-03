# Finam SDK for Laravel and PHP

[![CI](https://github.com/MasyaSmv/finam-sdk-laravel/actions/workflows/ci.yml/badge.svg)](https://github.com/MasyaSmv/finam-sdk-laravel/actions/workflows/ci.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/masyasmv/finam-sdk-laravel)](https://packagist.org/packages/masyasmv/finam-sdk-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/masyasmv/finam-sdk-laravel)](https://packagist.org/packages/masyasmv/finam-sdk-laravel)
[![License](https://img.shields.io/github/license/MasyaSmv/finam-sdk-laravel)](LICENSE)

PHP SDK для Finam Trade API с удобной работой через Laravel facade и без Laravel.

Пакет решает две задачи:

- даёт простой high-level API для типовых сценариев;
- оставляет низкоуровневый клиент, если нужен прямой доступ к REST-методам.

Документация Finam: <https://tradeapi.finam.ru/docs/about/>

## Для кого пакет

Пакет подойдёт, если ты хочешь:

- быстро получить котировки, инструменты, операции и заявки;
- работать не с сырыми массивами, а с DTO и коллекциями;
- использовать Laravel facade `Finam::...`;
- подключить тот же SDK в обычном PHP-проекте без Laravel.

## Что уже умеет пакет

- выпуск session token через `Finam::issueToken($secret)`
- подключение через secret одной командой: `Finam::connectSecret($secret)`
- подключение сессии через `Finam::connect($token)`
- низкоуровневый клиент через `Finam::client($token)`
- операции по счёту
- список заявок и получение одной заявки
- размещение market/limit orders
- размещение SL/TP orders
- инструменты, биржи, расписание, часы рынка
- котировки, свечи, стакан, последние сделки
- usage metrics
- создание отчёта и получение информации по отчёту
- typed DTO и typed collections
- Laravel service provider и facade

## Требования

- PHP `^8.0`
- Laravel `^8.0` для Laravel-режима
- `ext-json`

## Установка

```bash
composer require masyasmv/finam-sdk-laravel
```

Если ты используешь Laravel, service provider и facade подключатся автоматически.

## Быстрый старт

### Самый короткий путь

```php
use MasyaSmv\FinamSdk\Facades\Finam;

$session = Finam::connectSecret($secret);

$details = $session->sessionDetails();
$accountIds = $details->accountIds();
```

### Если нужен явный двухшаговый flow

```php
use MasyaSmv\FinamSdk\Facades\Finam;

$issued = Finam::issueToken($secret);
$sessionToken = $issued->token();
$session = Finam::connect($sessionToken);

$details = $session->sessionDetails();
$accountIds = $details->accountIds();

$quotes = $session->getLatestQuotes(['SBER@MISX', 'GAZP@MISX']);
$orders = $session->getOrders();
```

### 3. Получить счёт и использовать его дальше

Если в сессии только один счёт, `accountId` можно не передавать в часть методов.  
Если счетов несколько, передавай `accountId` явно.

```php
/** @var string $accountId */
$accountId = $details->accountIds()->first();

$operations = $session->getOperationsByDate(
    new DateTimeImmutable('2026-04-01'),
    new DateTimeImmutable('2026-04-03'),
    $accountId,
);

$order = $session->getOrder('123456789', $accountId);
```

## Примеры

### Laravel: котировки и стакан

```php
use MasyaSmv\FinamSdk\Facades\Finam;

$session = Finam::connect($token);

$quotes = $session->getLatestQuotes(['SBER@MISX', 'GAZP@MISX']);
$sber = $quotes->first();

$orderBook = $session->getOrderBook('SBER@MISX');
$bestRow = $orderBook->rows()->first();
```

### Laravel: свечи

```php
use DateTimeImmutable;
use MasyaSmv\FinamSdk\Dto\Market\CandlesQueryDto;
use MasyaSmv\FinamSdk\Facades\Finam;

$session = Finam::connect($token);

$candles = $session->getCandles(new CandlesQueryDto(
    symbol: 'SBER@MISX',
    timeframe: 'M1',
    startDate: new DateTimeImmutable('-1 hour'),
    endDate: new DateTimeImmutable('now'),
));
```

### Laravel: размещение заявки

```php
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderInputDto;
use MasyaSmv\FinamSdk\Facades\Finam;

$session = Finam::connect($token);

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

### Laravel: SL/TP заявка

```php
use MasyaSmv\FinamSdk\Dto\Order\PlaceSlTpOrderInputDto;
use MasyaSmv\FinamSdk\Facades\Finam;

$session = Finam::connect($token);

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

### Laravel: инструменты

```php
use MasyaSmv\FinamSdk\Facades\Finam;

$session = Finam::connect($token);

$instrument = $session->getInstrument('SBER@MISX', '1930918');
$allInstruments = $session->getInstruments();
$exchanges = $session->getExchanges();
$schedule = $session->getSchedule('SBER@MISX');
```

### Laravel: usage metrics

```php
use MasyaSmv\FinamSdk\Facades\Finam;

$session = Finam::connect($token);
$usage = $session->getUsageMetrics();

foreach ($usage->quotas() as $quota) {
    echo $quota->name() . ': ' . $quota->remaining() . PHP_EOL;
}
```

### Laravel: отчёты

```php
use DateTimeImmutable;
use MasyaSmv\FinamSdk\Dto\Report\CreateAccountReportInputDto;
use MasyaSmv\FinamSdk\Dto\Report\ReportDateRangeDto;
use MasyaSmv\FinamSdk\Facades\Finam;

$session = Finam::connect($token);

$created = $session->createAccountReport(new CreateAccountReportInputDto(
    accountId: '1930918',
    reportForm: 'REPORT_FORM_PDF',
    dateRange: new ReportDateRangeDto(
        from: new DateTimeImmutable('2026-04-01'),
        to: new DateTimeImmutable('2026-04-03'),
    ),
));

$info = $session->getAccountReportInfo($created->reportId());
```

## Обычный PHP без Laravel

Самый простой способ в обычном PHP-проекте: использовать низкоуровневый клиент.

### Низкоуровневый клиент

```php
use MasyaSmv\FinamSdk\Client\FinamClient;

$client = FinamClient::make($token);

$response = $client->get('/sessions/details');

if ($response->ok()) {
    $data = $response->data();
}
```

Если тебе нужен именно high-level session API вне Laravel, это тоже можно собрать вручную, но это уже более продвинутый сценарий. Для большинства plain PHP-проектов проще и понятнее начать с low-level клиента. Подробности есть в [docs/low-level-client.md](docs/low-level-client.md).

## Конфигурация

Пакет не хранит токен в конфиге.  
Токен всегда передаётся явно в runtime:

- `Finam::issueToken($secret)`
- `Finam::connectSecret($secret)`
- `Finam::connect($token)`
- `Finam::client($token)`

`config/finam.php` хранит только настройки транспорта:

```php
return [
    'base_url' => 'https://tradeapi.finam.ru/v1',
    'http' => [
        'timeout' => 10.0,
        'connect_timeout' => 5.0,
        'retries' => 0,
        'retry_delay_ms' => 200,
        'user_agent' => 'finam-sdk-laravel',
    ],
];
```

Опубликовать конфиг в Laravel можно так:

```bash
php artisan vendor:publish --tag=finam-config
```

## High-level API

Основной интерфейс сессии:

- `sessionDetails()`
- `getOperationsByDate($startDate, $endDate, ?$accountId = null, ?$limit = null)`
- `getOrders(?$accountId = null)`
- `getOrder($orderId, ?$accountId = null)`
- `placeOrder(PlaceOrderInputDto $order, ?$accountId = null)`
- `placeSlTpOrder(PlaceSlTpOrderInputDto $order, ?$accountId = null)`
- `getAllInstruments(?$cursor = null, bool $onlyActive = false, bool $onlyDisabled = false)`
- `getInstruments()`
- `getInstrument($symbol, ?$accountId = null)`
- `getExchanges()`
- `getClock()`
- `getSchedule($symbol)`
- `getLatestQuotes(array $symbols)`
- `getCandles(CandlesQueryDto $query)`
- `getOrderBook($symbol)`
- `getLatestTrades($symbol)`
- `getUsageMetrics()`
- `createAccountReport(CreateAccountReportInputDto $report)`
- `getAccountReportInfo($reportId)`

Все эти методы возвращают DTO или typed collections.

Для входа в high-level API есть два варианта:

- короткий shortcut: `Finam::connectSecret($secret)`
- явный вариант: `Finam::issueToken($secret)` и потом `Finam::connect($token)`

## Typed collections

Коллекции наследуются от `Illuminate\Support\Collection`, поэтому доступны привычные методы:

- `first()`
- `count()`
- `map()`
- `filter()`
- `pluck()`
- `all()`

Плюс у части коллекций есть удобные методы:

- `OrderCollection::findById($orderId)`
- `InstrumentCollection::findBySymbol($symbol)`

## Низкоуровневый клиент

Если high-level API тебе не подходит, можно работать напрямую через REST-клиент:

```php
use MasyaSmv\FinamSdk\Facades\Finam;

$client = Finam::client($token);

$response = $client->get('/accounts/1930918/orders');
```

Доступные resource wrappers:

- `$client->auth()`
- `$client->connect()`
- `$client->account()`
- `$client->order()`
- `$client->instrument()`
- `$client->market()`
- `$client->usageMetrics()`
- `$client->reports()`

## Ошибки

Основные типы исключений:

- `InvalidRequestException`  
  Когда входные данные некорректны ещё до отправки запроса.
- `ApiHttpException`  
  Когда Finam вернул HTTP-ошибку и её нужно обработать как ответ API.
- `ApiRequestFailedException`  
  Когда сломался transport-уровень: сеть, таймаут, невозможность выполнить запрос.
- `InvalidResponseException`  
  Когда сервер вернул битый или неожиданный ответ.
- `ResponseMappingException`  
  Когда ответ пришёл, но не соответствует ожидаемому shape.
- `AccountResolutionException`  
  Когда пакет не может сам выбрать счёт.

Пример:

```php
use MasyaSmv\FinamSdk\Exceptions\ApiHttpException;
use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

try {
    $quotes = Finam::connect($token)->getLatestQuotes(['SBER@MISX']);
} catch (InvalidRequestException $e) {
    report($e);
} catch (ApiHttpException $e) {
    report($e);
}
```

## Где смотреть полную документацию

- [Полная карта документации](docs/README.md)
- [Установка и настройка](docs/installation.md)
- [Быстрый старт](docs/quick-start.md)
- [High-level session API](docs/session-api.md)
- [Низкоуровневый клиент](docs/low-level-client.md)
- [Ошибки и диагностика](docs/errors.md)
- [FAQ](docs/faq.md)

## Тесты и качество

```bash
composer test
composer analyse
composer psalm
```

CI проверяет:

- `composer validate --strict`
- `phpunit`
- `phpstan`
- `psalm`
- матрицу `prefer-stable` и `prefer-lowest`

## Ограничения и честные ожидания

- пакет не делает бизнес-логику стратегии за тебя
- пакет не хранит токены
- пакет не открывает browser login flow сам
- для некоторых broker-side сценариев Finam может возвращать ограничения по правам, даже если SDK работает корректно
- отчёты уже поддерживаются, но конкретные значения `reportForm` стоит сверять с актуальной документацией и правами твоего аккаунта

## Лицензия

MIT. См. [LICENSE](LICENSE).
