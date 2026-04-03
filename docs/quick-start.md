# Быстрый старт

## Самый частый сценарий

1. Получаешь session token из secret
2. Подключаешь `Finam::connect($token)`
3. Получаешь DTO и коллекции

## Шаг 1. Выпустить token

```php
use MasyaSmv\FinamSdk\Facades\Finam;

$issued = Finam::issueToken($secret);
$token = $issued->token();
```

## Шаг 2. Подключить session API

```php
use MasyaSmv\FinamSdk\Facades\Finam;

$session = Finam::connect($token);
```

## Шаг 3. Проверить сессию

```php
$details = $session->sessionDetails();

$accountIds = $details->accountIds();
$readonly = $details->readonly();
```

## Шаг 4. Вызвать полезные методы

### Котировки

```php
$quotes = $session->getLatestQuotes(['SBER@MISX', 'GAZP@MISX']);
$firstQuote = $quotes->first();
```

### Инструмент

```php
$instrument = $session->getInstrument('SBER@MISX', '1930918');
```

### Заявки

```php
$orders = $session->getOrders('1930918');
$order = $orders->findById('123456789');
```

### Операции

```php
use DateTimeImmutable;

$operations = $session->getOperationsByDate(
    new DateTimeImmutable('2026-04-01'),
    new DateTimeImmutable('2026-04-03'),
    '1930918',
);
```

## Когда `accountId` можно не передавать

Часть методов принимает `?string $accountId = null`.

Это удобно, но есть важное правило:

- если в сессии ровно один счёт, пакет подставит его сам
- если счетов несколько, пакет выбросит `AccountResolutionException`

Если не хочешь сюрпризов, просто передавай `accountId` явно.

## Что возвращают методы

Пакет не старается возвращать сырые массивы, если можно вернуть объект.

Обычно ты получаешь:

- один DTO, например `OrderDto`
- коллекцию DTO, например `OrderCollection`

Коллекции совместимы с `Illuminate\Support\Collection`, поэтому доступны `first()`, `count()`, `map()`, `filter()` и другие привычные методы.
