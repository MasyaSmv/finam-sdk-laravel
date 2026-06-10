<?php

declare(strict_types = 1);

namespace MasyaSmv\FinamSdk\Client;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use MasyaSmv\FinamSdk\Api\Account\AccountApi;
use MasyaSmv\FinamSdk\Api\Auth\AuthApi;
use MasyaSmv\FinamSdk\Api\Connect\ConnectApi;
use MasyaSmv\FinamSdk\Api\Instrument\InstrumentApi;
use MasyaSmv\FinamSdk\Api\Market\MarketApi;
use MasyaSmv\FinamSdk\Api\Order\OrderApi;
use MasyaSmv\FinamSdk\Api\Reports\ReportsApi;
use MasyaSmv\FinamSdk\Api\UsageMetrics\UsageMetricsApi;
use MasyaSmv\FinamSdk\Auth\StaticTokenProvider;
use MasyaSmv\FinamSdk\Auth\TokenProviderInterface;
use MasyaSmv\FinamSdk\Dto\Transport\ApiError;
use MasyaSmv\FinamSdk\Dto\Transport\ApiHeaders;
use MasyaSmv\FinamSdk\Dto\Transport\ApiDiagnosticContext;
use MasyaSmv\FinamSdk\Dto\Transport\ApiMeta;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Dto\Transport\ApiRequestContext;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;
use MasyaSmv\FinamSdk\Exceptions\ApiRequestFailedException;
use MasyaSmv\FinamSdk\Exceptions\InvalidResponseException;
use Psr\Http\Message\ResponseInterface;

final class FinamClient
{
    public const DEFAULT_BASE_URL = 'https://tradeapi.finam.ru/v1/';

    /**
     * HTTP-статусы, которые считаем временными и повторяем при наличии попыток.
     * 429 — rate-limit, 503 — сервис недоступен.
     *
     * @var list<int>
     */
    private const RETRYABLE_STATUSES = [429, 503];

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
        private float $retryMultiplier = 1.0,
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

                // Временные статусы (429/503) повторяем с экспоненциальной паузой,
                // уважая Retry-After, пока остаются попытки. Иначе — отдаём
                // нормализованную ошибку ниже (без вечного цикла).
                if (in_array($status, self::RETRYABLE_STATUSES, true) && $attempt < $maxAttempts) {
                    $this->sleepBeforeRetry($attempt, $this->retryAfterMicroseconds($response));

                    continue;
                }

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
                        context: new ApiDiagnosticContext(
                            endpoint: $uri,
                            request: $this->meta($headers, $method, $uri, $options, $attempt)->request(),
                            headers: $this->meta($headers, $method, $uri, $options, $attempt)->headers(),
                            rawBody: mb_substr($body, 0, 2000),
                        ),
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
                        context: new ApiDiagnosticContext(
                            endpoint: $uri,
                            request: $this->requestContext($method, $uri, $options, $attempt),
                        ),
                        previous: $e,
                    );
                }

                $this->sleepBeforeRetry($attempt, null);
            }
        }
    }

    /**
     * Пауза перед повтором: либо Retry-After (если сервер прислал), либо
     * экспоненциальный backoff на базе retryDelayMs и retryMultiplier.
     */
    private function sleepBeforeRetry(int $attempt, ?int $retryAfterMicroseconds): void
    {
        $sleepMicroseconds = $retryAfterMicroseconds ?? $this->backoffMicroseconds($attempt);

        if ($sleepMicroseconds > 0) {
            usleep($sleepMicroseconds);
        }
    }

    /**
     * Экспоненциальная задержка: retryDelayMs * multiplier^(attempt-1), в микросекундах.
     */
    private function backoffMicroseconds(int $attempt): int
    {
        $baseMs = max(0, $this->retryDelayMs);
        $multiplier = $this->retryMultiplier > 0 ? $this->retryMultiplier : 1.0;
        $delayMs = (int) round($baseMs * ($multiplier ** max(0, $attempt - 1)));

        return $delayMs * 1000;
    }

    /**
     * Парсинг заголовка Retry-After (delta-seconds или HTTP-date) в микросекунды.
     * Возвращает null, если заголовок отсутствует/непарсится — тогда работает backoff.
     */
    private function retryAfterMicroseconds(ResponseInterface $response): ?int
    {
        if (!$response->hasHeader('Retry-After')) {
            return null;
        }

        $value = trim($response->getHeaderLine('Retry-After'));

        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            return (int) $value * 1_000_000;
        }

        $timestamp = strtotime($value);

        if ($timestamp === false) {
            return null;
        }

        $deltaSeconds = $timestamp - time();

        return $deltaSeconds > 0 ? $deltaSeconds * 1_000_000 : 0;
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
        /** @var array<string, string> $headers */
        $headers = (array)($options['headers'] ?? []);

        if ($token === '') {
            unset($headers['Authorization']);
        } else {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

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

    public function auth(): AuthApi
    {
        /** @var AuthApi $api */
        $api = $this->resource(AuthApi::class);

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

    public function usageMetrics(): UsageMetricsApi
    {
        /** @var UsageMetricsApi $api */
        $api = $this->resource(UsageMetricsApi::class);

        return $api;
    }

    public function reports(): ReportsApi
    {
        /** @var ReportsApi $api */
        $api = $this->resource(ReportsApi::class);

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
            request: $this->requestContext($method, $uri, $options, $attempt),
        );
    }

    /**
     * @param array<string, mixed> $options
     */
    private function requestContext(string $method, string $uri, array $options, int $attempt): ApiRequestContext
    {
        /** @var array<string, scalar|array<int|string, scalar|null>> $query */
        $query = is_array($options['query'] ?? null) ? $options['query'] : [];
        /** @var array<string, scalar|array<int|string, scalar|null>> $payload */
        $payload = is_array($options['json'] ?? null) ? $options['json'] : [];

        return new ApiRequestContext(
            method: $method,
            uri: $uri,
            query: new ApiPayload($query),
            payload: new ApiPayload($payload),
            attempt: $attempt,
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
