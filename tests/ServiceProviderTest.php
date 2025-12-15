<?php

namespace MasyaSmv\FinamSdk\Tests;

use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Auth\TokenProviderInterface;

/**
 * Проверяем, что провайдер:
 *  - подхватывает конфиг;
 *  - регистрирует клиента в контейнере;
 *  - даёт алиас.
 */
final class ServiceProviderTest extends TestCase
{
    /**
     * Конфиг пакета должен быть доступен сразу после регистрации провайдера.
     */
    public function test_config_is_available(): void
    {
        $this->assertSame('https://example.test', config('finam.base_url'));
        $this->assertSame('test-token', config('finam.token'));
        $this->assertSame(3.0, config('finam.http.timeout'));
    }

    /**
     * Проверяем, что FinamClient зарегистрирован в контейнере Laravel.
     */
    public function test_finam_client_is_bound_in_container(): void
    {
        $client = $this->app->make(FinamClient::class);

        $this->assertInstanceOf(FinamClient::class, $client);
    }

    /**
     * Проверяем наличие алиаса finam.sdk для удобного доступа.
     */
    public function test_alias_is_bound(): void
    {
        $clientByAlias = $this->app->make('finam.sdk');

        $this->assertInstanceOf(FinamClient::class, $clientByAlias);
    }

    /**
     * Провайдер токена должен разрешаться из контейнера и возвращать значение.
     */
    public function test_token_provider_is_resolved(): void
    {
        $provider = $this->app->make(TokenProviderInterface::class);

        $this->assertSame('test-token', $provider->getToken());
    }
}
