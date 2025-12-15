<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Auth;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Arr;
use MasyaSmv\FinamSdk\Exceptions\OAuthConfigurationException;
use MasyaSmv\FinamSdk\Exceptions\UnsupportedAuthDriverException;

/**
 * Менеджер для выбора провайдера токенов по конфигурации.
 *
 * Абстрагирует детали создания провайдеров и позволяет расширять список
 * драйверов без изменения пользовательского кода. Аналогично подходу
 * Laravel Manager, но облегчён для нужд SDK.
 */
final class TokenProviderManager
{
    private const DEFAULT_DRIVER = 'token';

    /**
     * @param array<string, mixed>   $config     Конфигурация SDK (config/finam.php).
     * @param CacheRepository|null   $cache      Опциональный кэш для OAuth токенов.
     * @param ClientInterface|null   $httpClient Внешний HTTP‑клиент (используется в тестах/DI).
     */
    public function __construct(
        private array $config,
        private ?CacheRepository $cache = null,
        private ?ClientInterface $httpClient = null,
    ) {
    }

    /**
     * Возвращает провайдер токенов согласно выбранному драйверу.
     *
     * @throws UnsupportedAuthDriverException Если передан неизвестный драйвер.
     * @throws OAuthConfigurationException    Если OAuth драйвер сконфигурирован некорректно.
     */
    public function driver(?string $driver = null): TokenProviderInterface
    {
        $driverName = $driver ?? (string) Arr::get($this->config, 'auth.driver', self::DEFAULT_DRIVER);

        return match ($driverName) {
            'token' => $this->createStaticProvider(),
            'oauth' => $this->createOAuthProvider(),
            default => throw new UnsupportedAuthDriverException(sprintf('Unsupported Finam auth driver [%s]', $driverName)),
        };
    }

    /**
     * Создаёт провайдер со статическим токеном.
     */
    private function createStaticProvider(): TokenProviderInterface
    {
        $tokenFromAuthConfig = Arr::get($this->config, 'auth.token.value');
        $tokenFallback = Arr::get($this->config, 'token', '');

        // Если auth.token.value не задан или пустой, используем корневой finam.token.
        // Это позволяет сохранять обратную совместимость с существующими .env,
        // где установлен только FINAM_TOKEN без секции auth.token.
        $token = is_string($tokenFromAuthConfig) && $tokenFromAuthConfig !== ''
            ? $tokenFromAuthConfig
            : (string) $tokenFallback;

        return new StaticTokenProvider($token);
    }

    /**
     * Создаёт OAuth провайдер с настройками из конфига.
     */
    private function createOAuthProvider(): TokenProviderInterface
    {
        $oauthConfig = (array) Arr::get($this->config, 'auth.oauth', []);

        $baseUri = (string) ($oauthConfig['base_url'] ?? Arr::get($this->config, 'base_url', ''));
        $tokenEndpoint = (string) ($oauthConfig['token_endpoint'] ?? '');
        $clientId = (string) ($oauthConfig['client_id'] ?? '');
        $clientSecret = (string) ($oauthConfig['client_secret'] ?? '');
        $grantType = (string) ($oauthConfig['grant_type'] ?? 'client_credentials');
        $scope = isset($oauthConfig['scope']) ? (string) $oauthConfig['scope'] : null;
        $cacheKey = (string) ($oauthConfig['cache_key'] ?? 'finam:auth:access_token');
        $cacheTtl = (int) ($oauthConfig['cache_ttl'] ?? 300);

        if ($tokenEndpoint === '') {
            throw new OAuthConfigurationException('Finam OAuth token_endpoint must be configured.');
        }

        $httpClient = $this->httpClient ?? new GuzzleClient([
            'base_uri' => $baseUri !== '' ? rtrim($baseUri, '/') . '/' : null,
            'timeout' => (float) Arr::get($this->config, 'http.timeout', 10.0),
            'connect_timeout' => (float) Arr::get($this->config, 'http.connect_timeout', 5.0),
            'headers' => [
                'User-Agent' => (string) Arr::get($this->config, 'http.user_agent', 'finam-sdk-laravel'),
                'Accept' => 'application/json',
            ],
        ]);

        return new OAuthTokenProvider(
            httpClient: $httpClient,
            tokenEndpoint: $tokenEndpoint,
            clientId: $clientId,
            clientSecret: $clientSecret,
            grantType: $grantType,
            scope: $scope,
            cache: $this->cache,
            cacheKey: $cacheKey,
            cacheTtl: $cacheTtl,
        );
    }
}
