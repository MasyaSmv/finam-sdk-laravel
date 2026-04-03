<?php

declare(strict_types = 1);

namespace MasyaSmv\FinamSdk\Client;

use MasyaSmv\FinamSdk\Auth\StaticTokenProvider;
use MasyaSmv\FinamSdk\Auth\TokenProviderInterface;
use MasyaSmv\FinamSdk\Dto\Config\FinamConfig;

final class FinamClientFactory
{
    public function __construct(
        private FinamConfig $config,
    ) {
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
        $httpConfig = $this->config->http();

        return new FinamClient(
            tokenProvider: $provider,
            baseUrl: $this->config->baseUrl(),
            timeout: $httpConfig->timeout(),
            connectTimeout: $httpConfig->connectTimeout(),
            retries: $httpConfig->retries(),
            retryDelayMs: $httpConfig->retryDelayMs(),
            userAgent: $httpConfig->userAgent(),
        );
    }
}
