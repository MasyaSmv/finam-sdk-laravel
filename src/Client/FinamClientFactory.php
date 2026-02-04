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
        $token = (string)($this->config['token'] ?? '');

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
        $httpConfig = (array)($this->config['http'] ?? []);

        return new FinamClient(
            tokenProvider: $provider,
            baseUrl: (string)($this->config['base_url'] ?? FinamClient::DEFAULT_BASE_URL),
            timeout: (float)($httpConfig['timeout'] ?? 10.0),
            connectTimeout: (float)($httpConfig['connect_timeout'] ?? 5.0),
            retries: (int)($httpConfig['retries'] ?? 0),
            retryDelayMs: (int)($httpConfig['retry_delay_ms'] ?? 200),
            userAgent: (string)($httpConfig['user_agent'] ?? 'finam-sdk-laravel'),
        );
    }
}
