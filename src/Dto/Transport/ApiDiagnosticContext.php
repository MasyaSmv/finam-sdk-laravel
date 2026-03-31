<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Transport;

final class ApiDiagnosticContext
{
    public function __construct(
        private ?string $endpoint = null,
        private ?ApiRequestContext $request = null,
        private ?ApiHeaders $headers = null,
        private ?string $requestId = null,
        private ?ApiPayload $errorPayload = null,
        private ?string $rawBody = null,
    ) {
    }

    public function endpoint(): ?string
    {
        return $this->endpoint;
    }

    public function request(): ?ApiRequestContext
    {
        return $this->request;
    }

    public function headers(): ?ApiHeaders
    {
        return $this->headers;
    }

    public function requestId(): ?string
    {
        return $this->requestId;
    }

    public function errorPayload(): ?ApiPayload
    {
        return $this->errorPayload;
    }

    public function rawBody(): ?string
    {
        return $this->rawBody;
    }
}
