<?php

namespace MasyaSmv\FinamSdk\Tests;

use Illuminate\Foundation\Application;
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
        $app['config']->set('finam.base_url', 'https://example.test');
        $app['config']->set('finam.token', 'test-token');
        $app['config']->set('finam.http.timeout', 3.0);
        $app['config']->set('finam.http.connect_timeout', 1.0);
        $app['config']->set('finam.http.retries', 1);
        $app['config']->set('finam.http.retry_delay_ms', 10);
        $app['config']->set('finam.http.user_agent', 'finam-sdk-tests');
    }
}
