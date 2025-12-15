<?php

namespace MasyaSmv\FinamSdk\Tests;

use MasyaSmv\FinamSdk\Client\FinamClient;

/**
 * Проверяем, что провайдер:
 *  - подхватывает конфиг;
 *  - регистрирует клиента в контейнере;
 *  - даёт алиас.
 */
final class ServiceProviderTest extends TestCase
{
    public function test_config_is_available(): void
    {
        $this->assertSame('https://example.test', config('finam.base_url'));
        $this->assertSame('test-token', config('finam.token'));
        $this->assertSame(3.0, config('finam.http.timeout'));
    }

    public function test_finam_client_is_bound_in_container(): void
    {
        $client = $this->app->make(FinamClient::class);

        $this->assertInstanceOf(FinamClient::class, $client);
    }

    public function test_alias_is_bound(): void
    {
        $clientByAlias = $this->app->make('finam.sdk');

        $this->assertInstanceOf(FinamClient::class, $clientByAlias);
    }
}
