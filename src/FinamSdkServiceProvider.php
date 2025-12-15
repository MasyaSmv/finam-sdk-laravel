<?php

namespace MasyaSmv\FinamSdk;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use MasyaSmv\FinamSdk\Auth\TokenProviderInterface;
use MasyaSmv\FinamSdk\Auth\TokenProviderManager;
use MasyaSmv\FinamSdk\Client\FinamClient;

/**
 * Service Provider пакета Finam SDK.
 *
 * Задачи:
 *  - регистрирует конфиг пакета (mergeConfigFrom);
 *  - публикует конфиг в приложение (vendor:publish);
 *  - регистрирует сервис (FinamClient) в контейнере Laravel;
 *
 * Важно:
 *  - Делает SDK удобным для использования в Laravel 8.
 *  - Внутри провайдера не должно быть бизнес-логики; только DI/конфиг/публикации.
 */
final class FinamSdkServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Регистрация биндингов в контейнер.
     *
     * Здесь мы:
     *  - мерджим конфиг (чтобы config('finam.*') работал сразу после установки);
     *  - регистрируем FinamClient как singleton, чтобы использовать один объект на запрос/процесс.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'finam');

        $this->app->singleton(TokenProviderManager::class, function ($app): TokenProviderManager {
            /** @var array<string, mixed> $cfg */
            $cfg = (array) $app['config']->get('finam', []);

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
            $cfg = (array) $app['config']->get('finam', []);

            /** @var TokenProviderInterface $tokenProvider */
            $tokenProvider = $app->make(TokenProviderInterface::class);

            return new FinamClient(
                baseUrl: (string) ($cfg['base_url'] ?? ''),
                token: $tokenProvider->getToken(),
                timeout: (float) ($cfg['http']['timeout'] ?? 10.0),
                connectTimeout: (float) ($cfg['http']['connect_timeout'] ?? 5.0),
                retries: (int) ($cfg['http']['retries'] ?? 0),
                retryDelayMs: (int) ($cfg['http']['retry_delay_ms'] ?? 200),
                userAgent: (string) ($cfg['http']['user_agent'] ?? 'finam-sdk-laravel'),
            );
        });

        // Дополнительно можно дать короткий алиас, если нравится:
        $this->app->alias(FinamClient::class, 'finam.sdk');
    }

    /**
     * Bootstrap-процессы.
     * Тут публикуем конфиг, чтобы команда vendor:publish могла его скопировать в приложение.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            $this->configPath() => $this->app->configPath('finam.php'),
        ], 'finam-config');
    }

    /**
     * Laravel может вызывать провайдер только когда нужны предоставляемые сервисы.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            TokenProviderManager::class,
            TokenProviderInterface::class,
            FinamClient::class,
            'finam.sdk',
        ];
    }

    /**
     * Локальный путь до конфига пакета.
     *
     * @return string
     */
    private function configPath(): string
    {
        return __DIR__ . '/../config/finam.php';
    }
}
