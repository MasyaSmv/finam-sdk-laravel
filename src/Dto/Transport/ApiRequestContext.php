<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Transport;

/**
 * @phpstan-import-type ApiNode from ApiPayload
 * @psalm-import-type ApiNode from ApiPayload
 */
final class ApiRequestContext
{
    public function __construct(
        private string $method,
        private string $uri,
        private ApiPayload $query,
        private ApiPayload $payload,
        private int $attempt,
    ) {
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function query(): ApiPayload
    {
        return $this->query;
    }

    public function payload(): ApiPayload
    {
        return $this->payload;
    }

    public function attempt(): int
    {
        return $this->attempt;
    }

    /**
     * @return array{
     *     method: string,
     *     uri: string,
     *     query: array<string, ApiNode>,
     *     payload: array<string, ApiNode>,
     *     attempt: int
     * }
     */
    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'uri' => $this->uri,
            'query' => $this->query->toArray(),
            'payload' => $this->payload->toArray(),
            'attempt' => $this->attempt,
        ];
    }
}
