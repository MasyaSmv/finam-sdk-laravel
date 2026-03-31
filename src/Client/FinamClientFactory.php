<?php

declare(strict_types = 1);

namespace MasyaSmv\FinamSdk\Client;

use MasyaSmv\FinamSdk\Auth\StaticTokenProvider;
use MasyaSmv\FinamSdk\Auth\TokenProviderInterface;

final class FinamClientFactory
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private array $config,
    ) {
    }

    public function default(): FinamClient
    {
        $token = $this->stringConfig($this->config, 'token', '');

        return $this->makeWithProvider(new StaticTokenProvider($token));
    }

    public function withToken(string $token): FinamClient
    {
        return $this->makeWithProvider(new StaticTokenProvider($token));
    }

    public function withTokenProvider(TokenProviderInterface $provider): FinamClient
    {
        return $this->makeWithProvider($provider);
    }

    private function makeWithProvider(TokenProviderInterface $provider): FinamClient
    {
        /** @var array<string, mixed> $httpConfig */
        $httpConfig = is_array($this->config['http'] ?? null) ? $this->config['http'] : [];

        return new FinamClient(
            tokenProvider: $provider,
            baseUrl: $this->stringConfig($this->config, 'base_url', FinamClient::DEFAULT_BASE_URL),
            timeout: $this->floatConfig($httpConfig, 'timeout', 10.0),
            connectTimeout: $this->floatConfig($httpConfig, 'connect_timeout', 5.0),
            retries: $this->intConfig($httpConfig, 'retries', 0),
            retryDelayMs: $this->intConfig($httpConfig, 'retry_delay_ms', 200),
            userAgent: $this->stringConfig($httpConfig, 'user_agent', 'finam-sdk-laravel'),
        );
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
