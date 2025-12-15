<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Auth;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use MasyaSmv\FinamSdk\Exceptions\OAuthTokenRequestException;
use MasyaSmv\FinamSdk\Exceptions\OAuthTokenResponseException;

/**
 * Провайдер, получающий OAuth токен по client_credentials.
 *
 * Встроено кэширование (при переданном CacheRepository) и fallback на
 * прямой запрос при отсутствии токена. Такой подход снижает нагрузку на
 * OAuth-сервер и уменьшает задержки в боевых сценариях.
 */
final class OAuthTokenProvider implements TokenProviderInterface
{
    /**
     * @param ClientInterface     $httpClient    HTTP‑клиент для обращения к OAuth серверу.
     * @param string              $tokenEndpoint Полный или относительный путь до токен-эндпоинта.
     * @param string              $clientId      OAuth client_id.
     * @param string              $clientSecret  OAuth client_secret.
     * @param string              $grantType     Тип гранта, по умолчанию client_credentials.
     * @param string|null         $scope         Скоупы, если требуются брокером.
     * @param CacheRepository|null $cache        Кэш для сохранения access_token.
     * @param string              $cacheKey      Ключ кэша для токена.
     * @param int                 $cacheTtl      Базовый TTL кэша (секунды), если expires_in не передан.
     */
    public function __construct(
        private ClientInterface $httpClient,
        private string $tokenEndpoint,
        private string $clientId,
        private string $clientSecret,
        private string $grantType = 'client_credentials',
        private ?string $scope = null,
        private ?CacheRepository $cache = null,
        private string $cacheKey = 'finam:auth:access_token',
        private int $cacheTtl = 300,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getToken(): string
    {
        if ($this->cache !== null) {
            $cached = $this->cache->get($this->cacheKey);
            if (is_string($cached) && $cached !== '') {
                return $cached;
            }
        }

        $token = $this->requestNewToken();

        if ($this->cache !== null && $token !== '') {
            $ttl = max(1, $this->cacheTtl);
            $this->cache->put($this->cacheKey, $token, $ttl);
        }

        return $token;
    }

    /**
     * Запрашивает новый токен у OAuth сервера.
     *
     * @throws OAuthTokenRequestException   При сетевой ошибке либо недоступности OAuth.
     * @throws OAuthTokenResponseException  При некорректной структуре или пустом access_token.
     */
    private function requestNewToken(): string
    {
        $payload = [
            'grant_type' => $this->grantType,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];

        if ($this->scope !== null) {
            $payload['scope'] = $this->scope;
        }

        try {
            $response = $this->httpClient->request('POST', $this->tokenEndpoint, [
                'form_params' => $payload,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
        } catch (GuzzleException $e) {
            // Оборачиваем сетевую ошибку в доменное исключение, чтобы потребитель
            // мог отдельно обработать недоступность OAuth сервиса.
            throw new OAuthTokenRequestException('Unable to request Finam OAuth token: ' . $e->getMessage(), 0, $e);
        }

        $decoded = json_decode((string) $response->getBody(), true);

        if (!is_array($decoded) || !isset($decoded['access_token'])) {
            // Явная проверка структуры ответа избавляет от неочевидных notice/undefined index
            // и даёт понятное сообщение пользователю SDK.
            throw new OAuthTokenResponseException('Finam OAuth token response is invalid or missing access_token.');
        }

        $token = (string) $decoded['access_token'];

        if ($token === '') {
            // Пустой access_token считаем логической ошибкой OAuth сервера/клиента,
            // поэтому возвращаем доменное исключение для упрощения диагностики.
            throw new OAuthTokenResponseException('Finam OAuth token is empty.');
        }

        $this->cacheTokenIfPossible($decoded, $token);

        return $token;
    }

    /**
     * Сохраняет токен в кэш с учётом указанного expires_in.
     *
     * @param array<string, mixed> $decoded Полный ответ OAuth, чтобы извлечь expires_in.
     * @param string               $token   Access token для сохранения.
     */
    private function cacheTokenIfPossible(array $decoded, string $token): void
    {
        if ($this->cache === null) {
            return;
        }

        $expiresIn = isset($decoded['expires_in']) && is_numeric($decoded['expires_in'])
            ? (int) $decoded['expires_in']
            : null;

        if ($expiresIn !== null && $expiresIn > 0) {
            // Делаем безопасный буфер в 30 секунд, чтобы не попасть в просроченный токен
            // из-за сетевых задержек или несовпадения часов между системами.
            $ttl = max(1, $expiresIn - 30);
            $this->cache->put($this->cacheKey, $token, $ttl);
        }
    }
}
