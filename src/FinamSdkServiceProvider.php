<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use MasyaSmv\FinamSdk\Auth\StaticTokenProvider;
use MasyaSmv\FinamSdk\Auth\TokenProviderInterface;
use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Client\FinamClientFactory;
use MasyaSmv\FinamSdk\Contracts\FinamManagerInterface;

final class FinamSdkServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'finam');

        $this->app->bind(TokenProviderInterface::class, function ($app): TokenProviderInterface {
            /** @var array<string, mixed> $cfg */
            $cfg = (array)$app['config']->get('finam', []);

            return new StaticTokenProvider($this->stringConfig($cfg, 'token', ''));
        });

        $this->app->singleton(FinamClientFactory::class, function ($app): FinamClientFactory {
            /** @var array<string, mixed> $cfg */
            $cfg = (array)$app['config']->get('finam', []);

            return new FinamClientFactory(
                config: $cfg,
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

            return new FinamManager($factory);
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
            FinamClientFactory::class,
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
}
