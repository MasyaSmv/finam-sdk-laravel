# Ошибки и диагностика

SDK специально разделяет ошибки по типам. Это помогает быстрее понять, где именно проблема.

## Основные исключения

### `InvalidRequestException`

Появляется, когда входные данные невалидны ещё до HTTP-запроса.

Примеры:

- пустой `symbol`
- пустой `accountId`
- дата начала позже даты конца
- у SL/TP заявки не задана ни одна ветка

### `ApiHttpException`

Появляется, когда Finam ответил HTTP-ошибкой.

Частые случаи:

- `401` token не подходит
- `403` нет прав на этот метод
- `429` превышен лимит
- `5xx` временная ошибка на стороне API

В объекте исключения есть полезные поля вроде `requestId`, `finamCode`, `finamMessage`.

### `ApiRequestFailedException`

Появляется, когда запрос вообще не удалось выполнить.

Например:

- таймаут
- сетевой сбой
- ошибка на transport-слое

### `InvalidResponseException`

Появляется, когда сервер вернул битый JSON или неожиданный ответ на transport-уровне.

### `ResponseMappingException`

Появляется, когда HTTP-ответ пришёл, но его невозможно привести к ожидаемому DTO.

### `AccountResolutionException`

Появляется, когда метод может сам выбрать счёт, но не может сделать это безопасно.

Например:

- в сессии нет ни одного счёта
- в сессии несколько счетов, а `accountId` не передан явно

## Пример обработки ошибок

```php
use MasyaSmv\FinamSdk\Exceptions\AccountResolutionException;
use MasyaSmv\FinamSdk\Exceptions\ApiHttpException;
use MasyaSmv\FinamSdk\Exceptions\ApiRequestFailedException;
use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;
use MasyaSmv\FinamSdk\Exceptions\InvalidResponseException;
use MasyaSmv\FinamSdk\Exceptions\ResponseMappingException;

try {
    $session = Finam::connect($token);
    $orders = $session->getOrders();
} catch (InvalidRequestException $e) {
    report($e);
} catch (AccountResolutionException $e) {
    report($e);
} catch (ApiHttpException $e) {
    report($e);
} catch (ApiRequestFailedException $e) {
    report($e);
} catch (InvalidResponseException $e) {
    report($e);
} catch (ResponseMappingException $e) {
    report($e);
}
```

## Как быстро понять, что сломалось

### Если ошибка сразу при создании DTO

Смотри на входные параметры.  
Скорее всего это `InvalidRequestException`.

### Если запрос дошёл до Finam и вернулся 401/403/429

Смотри на:

- корректность token
- права аккаунта
- лимиты API

### Если ошибка касается счёта

Передай `accountId` явно и проверь `sessionDetails()`.

### Если response shape внезапно изменился

Это уже повод проверить:

- актуальную документацию Finam
- реальный payload
- нужно ли обновить mapper в SDK

## Полезные шаги для отладки

1. Сначала вызови `sessionDetails()`
2. Убедись, что token живой
3. Убедись, что нужный `accountId` действительно есть в сессии
4. Для market/instrument методов проверь точный `symbol`, например `SBER@MISX`
5. Если нужен прямой контроль, временно используй low-level клиент
