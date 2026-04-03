# Finam SDK (Laravel)

[![CI](https://github.com/MasyaSmv/finam-sdk-laravel/actions/workflows/ci.yml/badge.svg)](https://github.com/MasyaSmv/finam-sdk-laravel/actions/workflows/ci.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/masyasmv/finam-sdk-laravel)](https://packagist.org/packages/masyasmv/finam-sdk-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/masyasmv/finam-sdk-laravel)](https://packagist.org/packages/masyasmv/finam-sdk-laravel)
[![License](https://img.shields.io/github/license/MasyaSmv/finam-sdk-laravel)](LICENSE)

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php\&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-8%2B-FF2D20?logo=laravel\&logoColor=white)
![Static Analysis](https://img.shields.io/badge/PHPStan-level%206-2f855a)

> Неофициальный SDK для работы с Finam Trade API (REST) с удобной интеграцией в Laravel 8+.
>
> Документация API: [https://tradeapi.finam.ru/docs/about/](https://tradeapi.finam.ru/docs/about/)

---

## Содержание

* [Зачем этот пакет](#зачем-этот-пакет)
* [Возможности](#возможности)
* [Требования](#требования)
* [Установка](#установка)

    * [Из Packagist](#из-packagist)
* [Настройка](#настройка)
* [Быстрый старт](#быстрый-старт)
* [Публикация конфига](#публикация-конфига)
* [Тесты и статический анализ](#тесты-и-статический-анализ)
* [Версионирование](#версионирование)
* [Безопасность](#безопасность)
* [Contributing](#contributing)
* [License](#license)

---

## Зачем этот пакет

Цель — дать удобный, типизированный, расширяемый SDK для взаимодействия с Finam Trade API из PHP/Laravel проектов.

Фокус:

* простой старт;
* аккуратный транспортный слой (REST);
* нормальные исключения и предсказуемое поведение;
* удобная интеграция в контейнер Laravel.

## Возможности

На текущем этапе пакет предоставляет базовую инфраструктуру:

* автоподключение Service Provider (Laravel package auto-discovery);
* конфигурация транспорта через `config/finam.php`;
* базовый REST-клиент на Guzzle с таймаутами и простыми ретраями;
* facade-first API через `Finam::connect($token)` и `Finam::client($token)`;
* тестовый стенд (Orchestra Testbench) для package-level тестов.

> Важно: это SDK. Бизнес-логика торговли, риск-менеджмент и «правильные решения» не входят в пакет.

## Требования

* PHP: `>= 8.0`
* Laravel: `>= 8.0`
* ext-json

## Установка

### Из Packagist

```bash
composer require masyasmv/finam-sdk-laravel
```

После этого:

```bash
composer update
```

## Настройка

Пакет читает транспортные настройки из `config('finam.*')`. Токен в конфиге не хранится: его нужно передавать явно в runtime.

Пример `config/finam.php`:

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

Токен всегда передаётся явно:

```php
use MasyaSmv\FinamSdk\Facades\Finam;

$session = Finam::connect($token);
$client = Finam::client($token);
```

## Быстрый старт

### High-level session API

```php
use MasyaSmv\FinamSdk\Facades\Finam;

$session = Finam::connect($token);

$details = $session->sessionDetails();
$quotes = $session->getLatestQuotes(['SBER@MISX', 'GAZP@MISX']);
$orders = $session->getOrders('account-id');
```

### Низкоуровневый клиент

Если нужен прямой доступ к transport-слою:

```php
use MasyaSmv\FinamSdk\Facades\Finam;

$client = Finam::client($token);
$response = $client->get('/sessions/details');
```

### Plain PHP

```php
use MasyaSmv\FinamSdk\Client\FinamClient;

$client = FinamClient::make($token);
$response = $client->get('/sessions/details');
```

### Фабрика клиентов

```php
use MasyaSmv\FinamSdk\Client\FinamClientFactory;

/** @var FinamClientFactory $factory */
$factory = app(FinamClientFactory::class);
$client = $factory->withToken($token);
```

## Публикация конфига

Если хочешь скопировать конфиг пакета в приложение:

```bash
php artisan vendor:publish --tag=finam-config
```

Файл появится как `config/finam.php`.

## Тесты и статический анализ

Запуск тестов:

```bash
composer test
```

Запуск PHPStan:

```bash
composer analyse
```

Запуск Psalm:

```bash
composer psalm
```

> GitHub Actions workflow `CI` запускает `composer validate --strict`, PHPStan, Psalm и тесты.

## Версионирование

Пакет следует семантическому версионированию (SemVer):

* `0.x` — активная разработка, API может меняться;
* `1.0.0` — стабилизация публичного API.

## Безопасность

* Никогда не коммить токены/секреты в репозиторий.
* Храни токены только в `.env` / секретах CI.
* При логировании ошибок не выводи токен целиком.

Если ты нашёл уязвимость или утечку секретов — создай приватное уведомление (security advisory) или issue без секретов.

## Contributing

PR приветствуются.

Рекомендации:

* добавляй тесты на новые сценарии;
* держи публичный API стабильным;
* избегай жёсткой привязки к конкретному приложению (SDK должен оставаться универсальным).

## License

MIT. См. файл [LICENSE](LICENSE).
