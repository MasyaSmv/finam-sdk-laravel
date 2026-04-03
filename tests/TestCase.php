<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use MasyaSmv\FinamSdk\FinamSdkServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Базовый TestCase для пакета.
 *
 * Orchestra Testbench поднимает минимальное Laravel-приложение,
 * чтобы тестировать package provider, config, container bindings и т.п.
 */
abstract class TestCase extends Orchestra
{
    /**
     * Регистрируем провайдер нашего пакета в тестовом приложении.
     *
     * @param Application $app
     *
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            FinamSdkServiceProvider::class,
        ];
    }

    /**
     * Подготовка окружения тестового приложения.
     *
     * Тут можно переопределять конфиги, env и т.д.
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        /** @var ConfigRepository $config */
        $config = $app['config'];

        $config->set('finam.base_url', 'https://example.test');
        $config->set('finam.http.timeout', 3.0);
        $config->set('finam.http.connect_timeout', 1.0);
        $config->set('finam.http.retries', 1);
        $config->set('finam.http.retry_delay_ms', 10);
        $config->set('finam.http.user_agent', 'finam-sdk-tests');
    }
}
