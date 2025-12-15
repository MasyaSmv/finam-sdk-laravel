<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;
use MasyaSmv\FinamSdk\Auth\TokenProviderManager;
use MasyaSmv\FinamSdk\Exceptions\OAuthConfigurationException;
use MasyaSmv\FinamSdk\Tests\TestCase;

/**
 * @covers \MasyaSmv\FinamSdk\Auth\TokenProviderManager
 */
final class TokenProviderManagerTest extends TestCase
{
    /**
     * Проверяем, что при драйвере token возвращается StaticTokenProvider
     * с переданным значением.
     */
    public function test_token_driver_returns_static_provider(): void
    {
        $manager = new TokenProviderManager([
            'auth' => [
                'driver' => 'token',
                'token' => ['value' => 'direct-token'],
            ],
        ]);

        $provider = $manager->driver();

        $this->assertSame('direct-token', $provider->getToken());
    }

    /**
     * Проверяем, что OAuth драйвер запрашивает токен и кладёт его в кэш.
     * MockHandler позволяет воспроизвести ответ Finam без реального HTTP.
     */
    public function test_oauth_driver_requests_and_caches_token(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'access_token' => 'oauth-token',
                'expires_in' => 120,
            ])),
        ]);

        $httpClient = new Client(['handler' => HandlerStack::create($mock)]);
        $cache = new CacheRepository(new ArrayStore());

        $manager = new TokenProviderManager(
            config: [
                'auth' => [
                    'driver' => 'oauth',
                    'oauth' => [
                        'token_endpoint' => '/oauth/token',
                        'client_id' => 'id',
                        'client_secret' => 'secret',
                        'cache_ttl' => 60,
                    ],
                ],
                'http' => [
                    'timeout' => 1.0,
                    'connect_timeout' => 1.0,
                    'user_agent' => 'tests',
                ],
            ],
            cache: $cache,
            httpClient: $httpClient,
        );

        $provider = $manager->driver('oauth');

        $first = $provider->getToken();
        $second = $provider->getToken();

        $this->assertSame('oauth-token', $first);
        $this->assertSame($first, $second);
        $this->assertSame('oauth-token', $cache->get('finam:auth:access_token'));
    }

    /**
     * Проверяем валидацию конфигурации: token_endpoint обязателен для OAuth.
     */
    public function test_oauth_driver_requires_endpoint(): void
    {
        $manager = new TokenProviderManager([
            'auth' => [
                'driver' => 'oauth',
                'oauth' => [
                    'client_id' => 'id',
                    'client_secret' => 'secret',
                ],
            ],
        ]);

        $this->expectException(OAuthConfigurationException::class);
        $this->expectExceptionMessage('token_endpoint');

        $manager->driver('oauth');
    }
}
