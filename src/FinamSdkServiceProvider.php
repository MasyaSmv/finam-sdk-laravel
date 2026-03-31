<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk;

use MasyaSmv\FinamSdk\Auth\AuthService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use MasyaSmv\FinamSdk\Auth\StaticTokenProvider;
use MasyaSmv\FinamSdk\Auth\TokenProviderInterface;
use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Client\FinamClientFactory;
use MasyaSmv\FinamSdk\Contracts\Api\AuthApiInterface;
use MasyaSmv\FinamSdk\Contracts\AuthServiceInterface;
use MasyaSmv\FinamSdk\Contracts\FinamManagerInterface;
use MasyaSmv\FinamSdk\Dto\Config\FinamConfig;
use MasyaSmv\FinamSdk\Dto\Config\FinamHttpConfig;
use MasyaSmv\FinamSdk\Session\Mapper\IssuedTokenMapper;
use MasyaSmv\FinamSdk\Session\Support\ApiResponseDecoder;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

final class FinamSdkServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'finam');

        $this->app->singleton(FinamConfig::class, function ($app): FinamConfig {
            /** @var array<string, mixed> $cfg */
            $cfg = (array) $app['config']->get('finam', []);
            /** @var array<string, mixed> $http */
            $http = is_array($cfg['http'] ?? null) ? $cfg['http'] : [];

            return new FinamConfig(
                baseUrl: $this->stringConfig($cfg, 'base_url', FinamClient::DEFAULT_BASE_URL),
                token: $this->stringConfig($cfg, 'token', ''),
                http: new FinamHttpConfig(
                    timeout: $this->floatConfig($http, 'timeout', 10.0),
                    connectTimeout: $this->floatConfig($http, 'connect_timeout', 5.0),
                    retries: $this->intConfig($http, 'retries', 0),
                    retryDelayMs: $this->intConfig($http, 'retry_delay_ms', 200),
                    userAgent: $this->stringConfig($http, 'user_agent', 'finam-sdk-laravel'),
                ),
            );
        });

        $this->app->bind(TokenProviderInterface::class, function ($app): TokenProviderInterface {
            /** @var FinamConfig $config */
            $config = $app->make(FinamConfig::class);

            return new StaticTokenProvider($config->token());
        });

        $this->app->singleton(FinamClientFactory::class, function ($app): FinamClientFactory {
            /** @var FinamConfig $config */
            $config = $app->make(FinamConfig::class);

            return new FinamClientFactory(
                config: $config,
            );
        });

        $this->app->singleton(AuthServiceInterface::class, function ($app): AuthServiceInterface {
            /** @var FinamClientFactory $factory */
            $factory = $app->make(FinamClientFactory::class);
            $reader = new ApiValueReader();

            return new AuthService(
                authApi: $factory->withToken('')->auth(),
                decoder: new ApiResponseDecoder($reader),
                mapper: new IssuedTokenMapper($reader),
            );
        });

        $this->app->bind(FinamClient::class, function ($app): FinamClient {
            /** @var FinamClientFactory $factory */
            $factory = $app->make(FinamClientFactory::class);

            return $factory->default();
        });

        $this->app->singleton(FinamManagerInterface::class, function ($app): FinamManager {
            /** @var FinamClientFactory $factory */
            $factory = $app->make(FinamClientFactory::class);
            /** @var AuthServiceInterface $authService */
            $authService = $app->make(AuthServiceInterface::class);

            return new FinamManager($factory, $authService);
        });

        $this->app->alias(FinamManagerInterface::class, 'finam');
        $this->app->alias(FinamClient::class, 'finam.sdk');
    }

    public function boot(): void
    {
        $this->publishes([
            $this->configPath() => $this->app->configPath('finam.php'),
        ], 'finam-config');
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            TokenProviderInterface::class,
            FinamConfig::class,
            FinamClientFactory::class,
            AuthServiceInterface::class,
            FinamClient::class,
            FinamManagerInterface::class,
            'finam',
            'finam.sdk',
        ];
    }

    private function configPath(): string
    {
        return __DIR__ . '/../config/finam.php';
    }

    /**
     * @param array<string, mixed> $config
     */
    private function stringConfig(array $config, string $key, string $default): string
    {
        $value = $config[$key] ?? $default;

        return is_scalar($value) ? (string) $value : $default;
    }

    /**
     * @param array<string, mixed> $config
     */
    private function floatConfig(array $config, string $key, float $default): float
    {
        $value = $config[$key] ?? $default;

        return is_int($value) || is_float($value) || is_string($value)
            ? (float) $value
            : $default;
    }

    /**
     * @param array<string, mixed> $config
     */
    private function intConfig(array $config, string $key, int $default): int
    {
        $value = $config[$key] ?? $default;

        return is_int($value) || is_float($value) || is_string($value)
            ? (int) $value
            : $default;
    }
}
