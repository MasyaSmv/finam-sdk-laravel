<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use MasyaSmv\FinamSdk\Api\Auth\AuthApi;
use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Auth\AuthService;
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
    public function testConfigIsAvailable(): void
    {
        $this->assertSame('https://example.test', config('finam.base_url'));
        $this->assertSame(3.0, config('finam.http.timeout'));
    }

    public function testClientFactoryIsBoundInContainer(): void
    {
        $factory = $this->app->make(FinamClientFactory::class);

        $this->assertInstanceOf(FinamClientFactory::class, $factory);
    }

    public function testManagerCanCreateRuntimeClient(): void
    {
        /** @var FinamManagerInterface $manager */
        $manager = $this->app->make(FinamManagerInterface::class);
        $client = $manager->client('runtime-token');

        $this->assertInstanceOf(FinamClient::class, $client);
        $this->assertSame('runtime-token', $client->getAccessToken());
    }

    public function testManagerIsBound(): void
    {
        $manager = $this->app->make(FinamManagerInterface::class);

        $this->assertInstanceOf(FinamManagerInterface::class, $manager);
    }

    public function testAuthServiceIsBound(): void
    {
        $service = $this->app->make(AuthServiceInterface::class);

        $this->assertInstanceOf(AuthServiceInterface::class, $service);
    }

    public function testFinamAliasIsBound(): void
    {
        $manager = $this->app->make('finam');

        $this->assertInstanceOf(FinamManagerInterface::class, $manager);
    }

    public function testTypedConfigIsResolved(): void
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

    public function testTypedConfigUsesDefaultsForInvalidScalarShapes(): void
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

    public function testFacadeConnectReturnsSession(): void
    {
        $session = Finam::connect('runtime-token');

        $this->assertInstanceOf(FinamSessionInterface::class, $session);
    }

    public function testFacadeConnectSecretIsForwardedToManager(): void
    {
        $session = $this->createMock(FinamSessionInterface::class);
        $manager = $this->createMock(FinamManagerInterface::class);
        $manager->expects($this->once())
            ->method('connectSecret')
            ->with('secret-token')
            ->willReturn($session);

        $this->app->instance('finam', $manager);
        Finam::clearResolvedInstance('finam');

        $this->assertSame($session, Finam::connectSecret('secret-token'));
    }

    public function testFacadeClientRequiresRuntimeToken(): void
    {
        $client = Finam::client('runtime-token');

        $this->assertInstanceOf(FinamClient::class, $client);
        $this->assertSame('runtime-token', $client->getAccessToken());
    }

    public function testProviderBootRegistersPublishPathAndDeclaresProvides(): void
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

    public function testProviderRegisterCanBeCalledDirectly(): void
    {
        $provider = new FinamSdkServiceProvider($this->app);
        $provider->register();

        $this->assertInstanceOf(FinamManagerInterface::class, $this->app->make(FinamManagerInterface::class));
    }

    public function testProviderPrivateHelpersAreCovered(): void
    {
        $provider = new FinamSdkServiceProvider($this->app);

        $configPath = new \ReflectionMethod(FinamSdkServiceProvider::class, 'configPath');
        $configPath->setAccessible(true);
        $stringConfig = new \ReflectionMethod(FinamSdkServiceProvider::class, 'stringConfig');
        $stringConfig->setAccessible(true);
        $floatConfig = new \ReflectionMethod(FinamSdkServiceProvider::class, 'floatConfig');
        $floatConfig->setAccessible(true);
        $intConfig = new \ReflectionMethod(FinamSdkServiceProvider::class, 'intConfig');
        $intConfig->setAccessible(true);

        /** @var string $resolvedConfigPath */
        $resolvedConfigPath = $configPath->invoke($provider);
        $this->assertStringEndsWith('/config/finam.php', $resolvedConfigPath);
        $this->assertSame('value', $stringConfig->invoke($provider, ['key' => 'value'], 'key', 'default'));
        $this->assertSame('default', $stringConfig->invoke($provider, ['key' => ['bad']], 'key', 'default'));
        $this->assertSame(2.5, $floatConfig->invoke($provider, ['key' => '2.5'], 'key', 1.0));
        $this->assertSame(1.0, $floatConfig->invoke($provider, ['key' => ['bad']], 'key', 1.0));
        $this->assertSame(3, $intConfig->invoke($provider, ['key' => '3'], 'key', 1));
        $this->assertSame(1, $intConfig->invoke($provider, ['key' => ['bad']], 'key', 1));
    }

    public function testAuthServiceBindingUsesEmptyTokenProviderForSecretFlow(): void
    {
        /** @var AuthServiceInterface $service */
        $service = $this->app->make(AuthServiceInterface::class);

        $authApiProperty = new \ReflectionProperty(AuthService::class, 'authApi');
        $authApiProperty->setAccessible(true);
        /** @var AuthApi $authApi */
        $authApi = $authApiProperty->getValue($service);

        $clientProperty = new \ReflectionProperty(AuthApi::class, 'client');
        $clientProperty->setAccessible(true);
        /** @var FinamClient $client */
        $client = $clientProperty->getValue($authApi);

        $providerProperty = new \ReflectionProperty(FinamClient::class, 'tokenProvider');
        $providerProperty->setAccessible(true);
        /** @var \MasyaSmv\FinamSdk\Auth\TokenProviderInterface $provider */
        $provider = $providerProperty->getValue($client);

        $this->assertSame('', $provider->getToken());
    }
}
