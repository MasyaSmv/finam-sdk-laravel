# Низкоуровневый клиент

Low-level API нужен, если ты хочешь:

- сам вызывать HTTP-методы
- работать через resource wrappers
- использовать пакет без facade
- отлаживать конкретный endpoint

## Самый простой способ

```php
use MasyaSmv\FinamSdk\Client\FinamClient;

$client = FinamClient::make($token);
```

## Прямой HTTP-вызов

```php
$response = $client->get('/sessions/details');

if ($response->ok()) {
    $data = $response->data();
}
```

Доступные методы клиента:

- `get(string $uri, array $query = [])`
- `post(string $uri, array $payload = [])`
- `delete(string $uri, array $query = [])`

Все они возвращают `ApiResponse`.

## Resource wrappers

Вместо ручного указания path можно использовать ресурсные обёртки:

- `$client->auth()`
- `$client->connect()`
- `$client->account()`
- `$client->order()`
- `$client->instrument()`
- `$client->market()`
- `$client->usageMetrics()`
- `$client->reports()`

Пример:

```php
$response = $client->account()->orders(
    new \MasyaSmv\FinamSdk\Dto\Order\OrdersRequest('1930918')
);
```

## Когда low-level API удобнее

- ты хочешь работать ближе к REST
- тебе нужен доступ к `ApiResponse`
- ты хочешь сам решать, как разбирать `data`, `error` и `meta`

## Что находится внутри `ApiResponse`

- `ok()`
- `status()`
- `data()`
- `error()`
- `meta()`

`meta()` полезен для диагностики: там есть headers и request context.

## Plain PHP без Laravel

Это основной вариант использования low-level клиента:

```php
use MasyaSmv\FinamSdk\Client\FinamClient;

$client = FinamClient::make($token);
$quotes = $client->market()->quotes(
    new \MasyaSmv\FinamSdk\Dto\Market\QuotesRequest(['SBER@MISX'])
);
```

Если у тебя уже есть session token, этого обычно достаточно.

## Когда всё же лучше взять high-level API

Если ты не хочешь читать shape каждого ответа и вручную думать, как его маппить, лучше использовать `Finam::connect($token)`.
