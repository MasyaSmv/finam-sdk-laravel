<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Auth\TokenProviderInterface;
use MasyaSmv\FinamSdk\Contracts\FinamManagerInterface;
use MasyaSmv\FinamSdk\Contracts\FinamSessionInterface;
use MasyaSmv\FinamSdk\Dto\Config\FinamConfig;
use MasyaSmv\FinamSdk\Facades\Finam;

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

    public function test_manager_is_bound(): void
    {
        $manager = $this->app->make(FinamManagerInterface::class);

        $this->assertInstanceOf(FinamManagerInterface::class, $manager);
    }

    public function test_finam_alias_is_bound(): void
    {
        $manager = $this->app->make('finam');

        $this->assertInstanceOf(FinamManagerInterface::class, $manager);
    }

    /**
     * Провайдер токена должен разрешаться из контейнера и возвращать значение.
     */
    public function test_token_provider_is_resolved(): void
    {
        /** @var TokenProviderInterface $provider */
        $provider = $this->app->make(TokenProviderInterface::class);

        $this->assertSame('test-token', $provider->getToken());
    }

    public function test_typed_config_is_resolved(): void
    {
        /** @var FinamConfig $config */
        $config = $this->app->make(FinamConfig::class);

        $this->assertSame('https://example.test', $config->baseUrl());
        $this->assertSame('test-token', $config->token());
        $this->assertSame(3.0, $config->http()->timeout());
        $this->assertSame(1.0, $config->http()->connectTimeout());
        $this->assertSame(1, $config->http()->retries());
        $this->assertSame(10, $config->http()->retryDelayMs());
        $this->assertSame('finam-sdk-tests', $config->http()->userAgent());
    }

    public function test_facade_connect_returns_session(): void
    {
        $session = Finam::connect('runtime-token');

        $this->assertInstanceOf(FinamSessionInterface::class, $session);
    }
}
