<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Config;

final class FinamConfig
{
    public function __construct(
        private string $baseUrl,
        private FinamHttpConfig $http,
    ) {
    }

    public function baseUrl(): string
    {
        return $this->baseUrl;
    }

    public function http(): FinamHttpConfig
    {
        return $this->http;
    }
}
