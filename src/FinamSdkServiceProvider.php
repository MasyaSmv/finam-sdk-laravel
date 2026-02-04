<?php

namespace MasyaSmv\FinamSdk;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use MasyaSmv\FinamSdk\Auth\TokenProviderInterface;
use MasyaSmv\FinamSdk\Auth\TokenProviderManager;
use MasyaSmv\FinamSdk\Client\FinamClient;

final class FinamSdkServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'finam');

        $this->app->singleton(TokenProviderManager::class, function ($app): TokenProviderManager {
            /** @var array<string, mixed> $cfg */
            $cfg = (array)$app['config']->get('finam', []);

            return new TokenProviderManager(
                config: $cfg,
                cache: $app->has('cache.store') ? $app->make('cache.store') : null,
            );
        });

        $this->app->bind(TokenProviderInterface::class, function ($app): TokenProviderInterface {
            /** @var TokenProviderManager $manager */
            $manager = $app->make(TokenProviderManager::class);

            return $manager->driver();
        });

        $this->app->singleton(FinamClient::class, function ($app): FinamClient {
            /** @var array<string, mixed> $cfg */
            $cfg = (array)$app['config']->get('finam', []);

            /** @var TokenProviderInterface $tokenProvider */
            $tokenProvider = $app->make(TokenProviderInterface::class);

            return new FinamClient(
                tokenProvider: $tokenProvider,
                baseUrl: (string)($cfg['base_url'] ?? FinamClient::DEFAULT_BASE_URL),
                timeout: (float)($cfg['http']['timeout'] ?? 10.0),
                connectTimeout: (float)($cfg['http']['connect_timeout'] ?? 5.0),
                retries: (int)($cfg['http']['retries'] ?? 0),
                retryDelayMs: (int)($cfg['http']['retry_delay_ms'] ?? 200),
                userAgent: (string)($cfg['http']['user_agent'] ?? 'finam-sdk-laravel'),
            );
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
            TokenProviderManager::class,
            TokenProviderInterface::class,
            FinamClient::class,
            'finam.sdk',
        ];
    }

    private function configPath(): string
    {
        return __DIR__ . '/../config/finam.php';
    }
}
