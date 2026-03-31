<?php

declare(strict_types = 1);

namespace MasyaSmv\FinamSdk\Client;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use MasyaSmv\FinamSdk\Api\Account\AccountApi;
use MasyaSmv\FinamSdk\Api\Connect\ConnectApi;
use MasyaSmv\FinamSdk\Api\Instrument\InstrumentApi;
use MasyaSmv\FinamSdk\Api\Market\MarketApi;
use MasyaSmv\FinamSdk\Api\Order\OrderApi;
use MasyaSmv\FinamSdk\Auth\StaticTokenProvider;
use MasyaSmv\FinamSdk\Auth\TokenProviderInterface;
use MasyaSmv\FinamSdk\Dto\Transport\ApiError;
use MasyaSmv\FinamSdk\Dto\Transport\ApiHeaders;
use MasyaSmv\FinamSdk\Dto\Transport\ApiMeta;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Dto\Transport\ApiRequestContext;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;
use MasyaSmv\FinamSdk\Exceptions\ApiRequestFailedException;
use MasyaSmv\FinamSdk\Exceptions\InvalidResponseException;

final class FinamClient
{
    public const DEFAULT_BASE_URL = 'https://api.finam.ru/v1/';

    private Guzzle $http;

    /** @var array<class-string, object> */
    private array $resources = [];

    public function __construct(
        TokenProviderInterface|string $tokenProvider,
        private string $baseUrl = self::DEFAULT_BASE_URL,
        private float $timeout = 10.0,
        private float $connectTimeout = 5.0,
        private int $retries = 0,
        private int $retryDelayMs = 200,
        private string $userAgent = 'finam-sdk-laravel',
    ) {
        $this->tokenProvider = is_string($tokenProvider)
            ? new StaticTokenProvider($tokenProvider)
            : $tokenProvider;

        // Guzzle создаём один раз. Authorization будем подставлять на каждый запрос
        // через options['headers'] ниже.
        $this->http = new Guzzle([
            'base_uri' => rtrim($this->baseUrl, '/') . '/',
            'timeout' => $this->timeout,
            'connect_timeout' => $this->connectTimeout,

            // Критично: не бросаем исключения на 4xx/5xx,
            // чтобы всегда иметь body и вернуть стабильный массив.
            'http_errors' => false,

            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => $this->userAgent,
            ],
        ]);
    }

    private TokenProviderInterface $tokenProvider;

    /**
     * Быстрый конструктор для НЕ-Laravel использования.
     * Сохраняем удобство, но внутри всё равно TokenProvider.
     *
     * @param string $token
     * @param string|null $baseUrl
     *
     * @return self
     */
    public static function make(string $token, ?string $baseUrl = null): self
    {
        return new self(
            tokenProvider: new StaticTokenProvider($token),
            baseUrl: $baseUrl ?? self::DEFAULT_BASE_URL,
        );
    }

    /**
     * Алиас для быстрого создания клиента с токеном.
     *
     * @param string $token
     * @param string|null $baseUrl
     *
     * @return self
     */
    public static function connectToken(string $token, ?string $baseUrl = null): self
    {
        return new self(
            tokenProvider: $token,
            baseUrl: $baseUrl ?? self::DEFAULT_BASE_URL,
        );
    }

    /**
     * Актуальный токен из провайдера (нужно ресурсам, например sessions/details).
     */
    public function getAccessToken(): string
    {
        return $this->tokenProvider->getToken();
    }

    /**
     * GET (нормализованный JSON).
     *
     * @param array<string, mixed> $query
     *
     * @return ApiResponse
     */
    public function get(string $uri, array $query = []): ApiResponse
    {
        return $this->requestJson('GET', $uri, ['query' => $query]);
    }

    /**
     * POST JSON (нормализованный JSON).
     *
     * @param array<string, mixed> $payload
     *
     * @return ApiResponse
     */
    public function post(string $uri, array $payload = []): ApiResponse
    {
        return $this->requestJson('POST', $uri, ['json' => $payload]);
    }

    /**
     * DELETE (нормализованный JSON).
     *
     * @param array<string, mixed> $query
     */
    public function delete(string $uri, array $query = []): ApiResponse
    {
        return $this->requestJson('DELETE', $uri, ['query' => $query]);
    }

    /**
     * Единая точка запроса: сетевой вызов + нормализация ответа.
     *
     * @param array<string, mixed> $options
     *
     * @return ApiResponse
     */
    public function requestJson(string $method, string $uri, array $options): ApiResponse
    {
        $attempt = 0;
        $maxAttempts = max(1, $this->retries + 1);

        $uri = ltrim($uri, '/');

        while (true) {
            $attempt++;

            // Подставляем актуальный Authorization на каждый запрос.
            // Токен может обновиться без пересоздания FinamClient.
            $options = $this->withAuthHeader($options);

            try {
                $response = $this->http->request($method, $uri, $options);

                $status = $response->getStatusCode();
                $headers = $response->getHeaders();

                $body = (string)$response->getBody();
                $trimmed = trim($body);

                $data = null;
                $jsonError = null;

                if ($trimmed !== '') {
                    $decoded = json_decode($trimmed, true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        // Гарантируем, что data — массив.
                        $data = is_array($decoded) ? $decoded : ['value' => $decoded];
                    } else {
                        $jsonError = json_last_error_msg();
                    }
                }

                // Если сервер вернул 2xx, но JSON битый — это нарушенный контракт => исключение
                if (($status >= 200 && $status < 300) && $jsonError !== null) {
                    throw new InvalidResponseException(
                        message: 'Response is not valid JSON: ' . $jsonError,
                        httpStatus: $status,
                        rawBody: mb_substr($body, 0, 2000),
                    );
                }

                $ok = ($status >= 200 && $status < 300) && ($jsonError === null);

                if ($ok) {
                    return new ApiResponse(
                        ok: true,
                        status: $status,
                        data: new ApiPayload($data ?? []),
                        error: null,
                        meta: $this->meta($headers, $method, $uri, $options, $attempt),
                    );
                }

                $details = is_array($data) ? new ApiPayload($data) : null;
                $errorMessage = $this->resolveErrorMessage($status, $jsonError, $details);

                return new ApiResponse(
                    ok: false,
                    status: $status,
                    data: null,
                    error: new ApiError(
                        message: $errorMessage,
                        type: $this->guessErrorType($status, $jsonError),
                        details: $details,
                        raw: $jsonError !== null ? mb_substr($body, 0, 2000) : null,
                    ),
                    meta: $this->meta($headers, $method, $uri, $options, $attempt),
                );
            } catch (GuzzleException $e) {
                if ($attempt >= $maxAttempts) {
                    throw new ApiRequestFailedException(
                        sprintf('Finam API request failed after %d attempt(s): %s', $attempt, $e->getMessage()),
                        previous: $e,
                    );
                }

                $sleepMicroseconds = max(0, $this->retryDelayMs) * 1000;

                usleep($sleepMicroseconds);
            }
        }
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function withAuthHeader(array $options): array
    {
        $token = $this->getAccessToken();

        // Если провайдер вернул пусто — лучше не лепить "Bearer ".
        // Пусть API вернёт 401, а мы отдадим нормализованную ошибку.
        $auth = $token !== '' ? ('Bearer ' . $token) : '';

        /** @var array<string, string> $headers */
        $headers = (array)($options['headers'] ?? []);
        $headers['Authorization'] = $auth;

        $options['headers'] = $headers;

        return $options;
    }

    private function guessErrorType(int $status, ?string $jsonError): ?string
    {
        if ($jsonError !== null) {
            return 'invalid_json';
        }

        return match (true) {
            $status === 401 || $status === 403 => 'auth',
            $status === 404 => 'not_found',
            $status >= 400 && $status < 500 => 'client',
            $status >= 500 => 'server',
            default => null,
        };
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private function resource(string $class)
    {
        if (isset($this->resources[$class])) {
            /** @var T $resource */
            $resource = $this->resources[$class];

            return $resource;
        }

        // Защита от опечаток / несуществующих классов
        if (!class_exists($class)) {
            throw new InvalidArgumentException("API resource class does not exist: {$class}");
        }

        $instance = new $class($this);

        $this->resources[$class] = $instance;

        /** @var T $resource */
        $resource = $instance;

        return $resource;
    }

    public function connect(): ConnectApi
    {
        /** @var ConnectApi $api */
        $api = $this->resource(ConnectApi::class);

        return $api;
    }

    public function account(): AccountApi
    {
        /** @var AccountApi $api */
        $api = $this->resource(AccountApi::class);

        return $api;
    }

    public function instrument(): InstrumentApi
    {
        /** @var InstrumentApi $api */
        $api = $this->resource(InstrumentApi::class);

        return $api;
    }

    public function order(): OrderApi
    {
        /** @var OrderApi $api */
        $api = $this->resource(OrderApi::class);

        return $api;
    }

    public function market(): MarketApi
    {
        /** @var MarketApi $api */
        $api = $this->resource(MarketApi::class);

        return $api;
    }

    /**
     * @param array<array-key, array<array-key, string>> $headers
     * @param array<string, mixed> $options
     */
    private function meta(array $headers, string $method, string $uri, array $options, int $attempt): ApiMeta
    {
        $normalizedHeaders = [];

        foreach ($headers as $name => $values) {
            if (!is_string($name)) {
                continue;
            }

            $normalizedHeaders[$name] = array_values(
                array_filter(
                    $values,
                    static fn ($value): bool => is_string($value),
                ),
            );
        }

        /** @var array<string, scalar|array<int|string, scalar|null>> $query */
        $query = is_array($options['query'] ?? null) ? $options['query'] : [];
        /** @var array<string, scalar|array<int|string, scalar|null>> $payload */
        $payload = is_array($options['json'] ?? null) ? $options['json'] : [];

        return new ApiMeta(
            headers: new ApiHeaders($normalizedHeaders),
            request: new ApiRequestContext(
                method: $method,
                uri: $uri,
                query: new ApiPayload($query),
                payload: new ApiPayload($payload),
                attempt: $attempt,
            ),
        );
    }

    private function resolveErrorMessage(int $status, ?string $jsonError, ?ApiPayload $details): string
    {
        if ($jsonError !== null) {
            return 'Response is not valid JSON: ' . $jsonError;
        }

        $message = $details?->firstStringByKeys('message', 'error', 'description', 'detail');

        if ($message !== null) {
            return $message;
        }

        return 'HTTP error: ' . $status;
    }
}
