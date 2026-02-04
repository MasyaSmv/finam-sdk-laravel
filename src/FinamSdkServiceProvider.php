<?php

namespace MasyaSmv\FinamSdk;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use MasyaSmv\FinamSdk\Auth\StaticTokenProvider;
use MasyaSmv\FinamSdk\Auth\TokenProviderInterface;
use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Client\FinamClientFactory;

final class FinamSdkServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'finam');

        $this->app->bind(TokenProviderInterface::class, function ($app): TokenProviderInterface {
            /** @var array<string, mixed> $cfg */
            $cfg = (array)$app['config']->get('finam', []);

            return new StaticTokenProvider((string)($cfg['token'] ?? ''));
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

        $this->app->alias(FinamClient::class, 'finam.sdk');
    }

    public function boot(): void
    {
        $this->publishes([
            $this->configPath() => $this->app->configPath('finam.php'),
        ], 'finam-config');
    }

    public function provides(): array
    {
        return [
            TokenProviderInterface::class,
            FinamClientFactory::class,
            FinamClient::class,
            'finam.sdk',
        ];
    }

    private function configPath(): string
    {
        return __DIR__ . '/../config/finam.php';
    }
}
