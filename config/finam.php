<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Finam Trade API base URL
    |--------------------------------------------------------------------------
    |
    | Базовый URL для REST API.
    | Оставил возможность переопределения через ENV.
    |
    */
    'base_url' => env('FINAM_BASE_URL', 'https://trade-api.finam.ru'),

    /*
    |--------------------------------------------------------------------------
    | Authentication token
    |--------------------------------------------------------------------------
    |
    | Токен доступа (или ключ), который ты будешь использовать для запросов.
    | Нельзя хардкодить в коде — только через ENV.
    |
    */
    'token' => env('FINAM_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Authentication drivers
    |--------------------------------------------------------------------------
    |
    | driver: token|oauth
    |   token: статический Bearer-токен из личного кабинета Finam или .env
    |   oauth: запрос access_token через Auth Service (поддержка client_credentials)
    |
    | oauth:
    |   base_url: базовый URL Auth Service (по умолчанию совпадает с base_url API)
    |   token_endpoint: относительный путь до эндпоинта получения токена
    |   client_id / client_secret: учётные данные приложения
    |   grant_type: client_credentials (по умолчанию) или иной, если потребуется
    |   scope: при необходимости сузить доступ
    |   cache_key/cache_ttl: настройки кеширования access_token (Redis/array)
    */
    'auth' => [
        'driver' => env('FINAM_AUTH_DRIVER', 'token'),

        'token' => [
            'value' => env('FINAM_TOKEN', ''),
        ],

        'oauth' => [
            'base_url' => env('FINAM_AUTH_BASE_URL', env('FINAM_BASE_URL', 'https://trade-api.finam.ru')),
            'token_endpoint' => env('FINAM_AUTH_TOKEN_ENDPOINT', '/auth/oauth2/v1/token'),
            'client_id' => env('FINAM_AUTH_CLIENT_ID'),
            'client_secret' => env('FINAM_AUTH_CLIENT_SECRET'),
            'grant_type' => env('FINAM_AUTH_GRANT_TYPE', 'client_credentials'),
            'scope' => env('FINAM_AUTH_SCOPE'),
            'cache_key' => env('FINAM_AUTH_CACHE_KEY', 'finam:auth:access_token'),
            'cache_ttl' => (int) env('FINAM_AUTH_CACHE_TTL', 300),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP client settings
    |--------------------------------------------------------------------------
    |
    | Настройки транспорта:
    |  - timeout: общий таймаут запроса
    |  - connect_timeout: таймаут установки соединения
    |  - retries: количество повторов при временных сетевых сбоях
    |  - retry_delay_ms: задержка между повторами (мс)
    |  - user_agent: полезно для логов провайдера API
    |
    */
    'http' => [
        'timeout' => (float) env('FINAM_HTTP_TIMEOUT', 10),
        'connect_timeout' => (float) env('FINAM_HTTP_CONNECT_TIMEOUT', 5),

        'retries' => (int) env('FINAM_HTTP_RETRIES', 0),
        'retry_delay_ms' => (int) env('FINAM_HTTP_RETRY_DELAY_MS', 200),

        'user_agent' => env('FINAM_HTTP_USER_AGENT', 'finam-sdk-laravel'),
    ],
];
