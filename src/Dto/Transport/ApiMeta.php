<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Transport;

final class ApiMeta
{
    public function __construct(
        private ApiHeaders $headers,
        private ?ApiRequestContext $request = null,
    ) {
    }

    public function headers(): ApiHeaders
    {
        return $this->headers;
    }

    public function request(): ?ApiRequestContext
    {
        return $this->request;
    }
}
