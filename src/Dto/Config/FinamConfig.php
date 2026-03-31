<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Config;

final class FinamConfig
{
    public function __construct(
        private string $baseUrl,
        private string $token,
        private FinamHttpConfig $http,
    ) {
    }

    public function baseUrl(): string
    {
        return $this->baseUrl;
    }

    public function token(): string
    {
        return $this->token;
    }

    public function http(): FinamHttpConfig
    {
        return $this->http;
    }
}
