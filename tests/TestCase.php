<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use MasyaSmv\FinamSdk\FinamSdkServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Базовый TestCase для пакета.
 *
 * Orchestra Testbench поднимает минимальное Laravel-приложение,
 * чтобы тестировать package provider, config, container bindings и т.п.
 */
abstract class TestCase extends Orchestra
{
    /**
     * Compatibility shim for older orchestra/testbench releases pulled by prefer-lowest.
     *
     * @return array{class: array<string, list<string>>, method: array<string, list<string>>}
     */
    public function getAnnotations(): array
    {
        return [
            'class' => $this->parseAnnotations((new ReflectionClass(static::class))->getDocComment() ?: ''),
            'method' => $this->parseMethodAnnotations(),
        ];
    }

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

    /**
     * @return array<string, list<string>>
     */
    private function parseMethodAnnotations(): array
    {
        try {
            $method = new ReflectionMethod($this, $this->currentTestMethodName());
        } catch (ReflectionException) {
            return [];
        }

        return $this->parseAnnotations($method->getDocComment() ?: '');
    }

    /**
     * @return array<string, list<string>>
     */
    private function parseAnnotations(string $docComment): array
    {
        $annotations = [];

        preg_match_all('/@([A-Za-z_\\\\-]+)(?:[ \t]+([^\r\n*]+))?/', $docComment, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $name = $match[1];
            $value = trim($match[2] ?? '');

            $annotations[$name] ??= [];
            $annotations[$name][] = $value;
        }

        return $annotations;
    }

    private function currentTestMethodName(): string
    {
        $property = new ReflectionProperty(Orchestra::class, 'name');
        $property->setAccessible(true);

        /** @var string $name */
        $name = $property->getValue($this);

        return $name;
    }
}
