# Установка и настройка

## Установка

```bash
composer require masyasmv/finam-sdk-laravel
```

Если у тебя Laravel, этого обычно достаточно.  
Service provider и facade подключатся автоматически.

## Что настраивается в конфиге

Пакет хранит в конфиге только transport-настройки.

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

## Что не хранится в конфиге

Токен в конфиге не хранится.

Это важное правило пакета:

- `secret` передаётся в `Finam::issueToken($secret)`
- `token` передаётся в `Finam::connect($token)` или `Finam::client($token)`

Так проще не допустить случайную утечку токена через репозиторий или общий конфиг.

## Публикация конфига в Laravel

```bash
php artisan vendor:publish --tag=finam-config
```

После этого в проекте появится `config/finam.php`.

## Минимальные требования

- PHP `^8.0`
- Laravel `^8.0`, если используешь Laravel
- `ext-json`

## Что ещё нужно до первого запроса

Тебе понадобится один из двух вариантов:

- session token, если он уже есть
- secret, если ты хочешь сначала получить session token через SDK
