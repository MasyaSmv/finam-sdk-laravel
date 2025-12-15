<?php

namespace MasyaSmv\FinamSdk\Client;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use MasyaSmv\FinamSdk\Exceptions\ApiRequestFailedException;
use Psr\Http\Message\ResponseInterface;

/**
 * Минимальный HTTP-клиент для работы с Finam API.
 *
 * Инкапсулирует настройки таймаутов, user-agent и стратегию ретраев, чтобы
 * обеспечить предсказуемое поведение при сетевых сбоях.
 */
final class FinamClient
{
    private Guzzle $http;

    /**
     * @param string $baseUrl        Базовый URL API Finam.
     * @param string $token          Токен авторизации (будет проставлен в Authorization).
     * @param float  $timeout        Таймаут выполнения запроса.
     * @param float  $connectTimeout Таймаут установки соединения.
     * @param int    $retries        Количество дополнительных попыток при сетевых ошибках.
     * @param int    $retryDelayMs   Пауза между попытками (в миллисекундах).
     * @param string $userAgent      Значение заголовка User-Agent.
     */
    public function __construct(
        private string $baseUrl,
        private string $token,
        private float $timeout = 10.0,
        private float $connectTimeout = 5.0,
        private int $retries = 0,
        private int $retryDelayMs = 200,
        private string $userAgent = 'finam-sdk-laravel',
    ) {
        $this->http = new Guzzle([
            'base_uri' => rtrim($this->baseUrl, '/') . '/',
            'timeout' => $this->timeout,
            'connect_timeout' => $this->connectTimeout,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => $this->userAgent,
                'Authorization' => $this->token !== '' ? ('Bearer ' . $this->token) : '',
            ],
        ]);
    }

    /**
     * Простейший GET.
     * Потом можно расширить до request(method, uri, options), добавить middleware, логирование и т.д.
     *
     * @param string               $uri   URI относительно baseUrl.
     * @param array<string, mixed> $query Параметры запроса.
     */
    public function get(string $uri, array $query = []): ResponseInterface
    {
        return $this->requestWithRetries('GET', $uri, ['query' => $query]);
    }

    /**
     * Единая точка вызова с ретраями.
     *
     * Реализовано максимально просто:
     *  - повторяем попытки при сетевых исключениях;
     *  - бизнес-ошибки (400/422) сюда не относятся — их обычно не ретраят.
     *
     * @param string               $method  HTTP метод.
     * @param string               $uri     URI относительно baseUrl.
     * @param array<string, mixed> $options Параметры запроса для Guzzle.
     *
     * @throws ApiRequestFailedException После исчерпания всех ретраев.
     */
    private function requestWithRetries(string $method, string $uri, array $options): ResponseInterface
    {
        $attempt = 0;
        $maxAttempts = max(1, $this->retries + 1);

        while (true) {
            $attempt++;

            try {
                return $this->http->request($method, ltrim($uri, '/'), $options);
            } catch (GuzzleException $e) {
                if ($attempt >= $maxAttempts) {
                    throw new ApiRequestFailedException(
                        sprintf('Finam API request failed after %d attempt(s): %s', $attempt, $e->getMessage()),
                        previous: $e,
                    );
                }

                usleep($this->retryDelayMs * 1000);
            }
        }
    }

    /**
     * Простейший POST JSON.
     *
     * @param string               $uri     URI относительно baseUrl.
     * @param array<string, mixed> $payload JSON-параметры запроса.
     */
    public function post(string $uri, array $payload = []): ResponseInterface
    {
        return $this->requestWithRetries('POST', $uri, ['json' => $payload]);
    }
}
