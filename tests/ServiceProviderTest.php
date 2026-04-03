<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Contracts\AuthServiceInterface;
use MasyaSmv\FinamSdk\Contracts\FinamManagerInterface;
use MasyaSmv\FinamSdk\Contracts\FinamSessionInterface;
use MasyaSmv\FinamSdk\Client\FinamClientFactory;
use MasyaSmv\FinamSdk\Dto\Config\FinamConfig;
use MasyaSmv\FinamSdk\Facades\Finam;
use MasyaSmv\FinamSdk\FinamSdkServiceProvider;

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
        $this->assertSame(3.0, config('finam.http.timeout'));
    }

    public function test_client_factory_is_bound_in_container(): void
    {
        $factory = $this->app->make(FinamClientFactory::class);

        $this->assertInstanceOf(FinamClientFactory::class, $factory);
    }

    public function test_manager_can_create_runtime_client(): void
    {
        /** @var FinamManagerInterface $manager */
        $manager = $this->app->make(FinamManagerInterface::class);
        $client = $manager->client('runtime-token');

        $this->assertInstanceOf(FinamClient::class, $client);
        $this->assertSame('runtime-token', $client->getAccessToken());
    }

    public function test_manager_is_bound(): void
    {
        $manager = $this->app->make(FinamManagerInterface::class);

        $this->assertInstanceOf(FinamManagerInterface::class, $manager);
    }

    public function test_auth_service_is_bound(): void
    {
        $service = $this->app->make(AuthServiceInterface::class);

        $this->assertInstanceOf(AuthServiceInterface::class, $service);
    }

    public function test_finam_alias_is_bound(): void
    {
        $manager = $this->app->make('finam');

        $this->assertInstanceOf(FinamManagerInterface::class, $manager);
    }

    public function test_typed_config_is_resolved(): void
    {
        /** @var FinamConfig $config */
        $config = $this->app->make(FinamConfig::class);

        $this->assertSame('https://example.test', $config->baseUrl());
        $this->assertSame(3.0, $config->http()->timeout());
        $this->assertSame(1.0, $config->http()->connectTimeout());
        $this->assertSame(1, $config->http()->retries());
        $this->assertSame(10, $config->http()->retryDelayMs());
        $this->assertSame('finam-sdk-tests', $config->http()->userAgent());
    }

    public function test_typed_config_uses_defaults_for_invalid_scalar_shapes(): void
    {
        $this->app->forgetInstance(FinamConfig::class);
        /** @var ConfigRepository $config */
        $config = $this->app['config'];
        $config->set('finam.base_url', ['bad']);
        $config->set('finam.http', [
            'timeout' => ['bad'],
            'connect_timeout' => ['bad'],
            'retries' => ['bad'],
            'retry_delay_ms' => ['bad'],
            'user_agent' => ['bad'],
        ]);

        /** @var FinamConfig $config */
        $config = $this->app->make(FinamConfig::class);

        $this->assertSame(FinamClient::DEFAULT_BASE_URL, $config->baseUrl());
        $this->assertSame(10.0, $config->http()->timeout());
        $this->assertSame(5.0, $config->http()->connectTimeout());
        $this->assertSame(0, $config->http()->retries());
        $this->assertSame(200, $config->http()->retryDelayMs());
        $this->assertSame('finam-sdk-laravel', $config->http()->userAgent());
    }

    public function test_facade_connect_returns_session(): void
    {
        $session = Finam::connect('runtime-token');

        $this->assertInstanceOf(FinamSessionInterface::class, $session);
    }

    public function test_facade_client_requires_runtime_token(): void
    {
        $client = Finam::client('runtime-token');

        $this->assertInstanceOf(FinamClient::class, $client);
        $this->assertSame('runtime-token', $client->getAccessToken());
    }

    public function test_provider_boot_registers_publish_path_and_declares_provides(): void
    {
        $provider = new FinamSdkServiceProvider($this->app);
        $provider->boot();

        $this->assertSame([
            FinamConfig::class,
            FinamClientFactory::class,
            AuthServiceInterface::class,
            FinamManagerInterface::class,
            'finam',
        ], $provider->provides());

        $paths = FinamSdkServiceProvider::pathsToPublish(FinamSdkServiceProvider::class, 'finam-config');
        $this->assertContains($this->app->configPath('finam.php'), $paths);
    }
}
