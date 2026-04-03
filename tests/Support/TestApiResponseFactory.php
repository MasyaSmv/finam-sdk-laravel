<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests\Support;

use MasyaSmv\FinamSdk\Dto\Transport\ApiError;
use MasyaSmv\FinamSdk\Dto\Transport\ApiHeaders;
use MasyaSmv\FinamSdk\Dto\Transport\ApiMeta;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Dto\Transport\ApiRequestContext;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

final class TestApiResponseFactory
{
    /**
     * @param array<string, mixed> $response
     */
    public static function fromArray(array $response): ApiResponse
    {
        $meta = is_array($response['meta'] ?? null) ? $response['meta'] : [];
        $headers = is_array($meta['headers'] ?? null) ? $meta['headers'] : [];
        $request = is_array($meta['request'] ?? null) ? $meta['request'] : null;
        $error = is_array($response['error'] ?? null) ? $response['error'] : null;
        $data = is_array($response['data'] ?? null) ? $response['data'] : null;

        return new ApiResponse(
            ok: ($response['ok'] ?? true) === true,
            status: is_int($response['status'] ?? null) ? $response['status'] : 200,
            data: $data === null ? null : new ApiPayload($data),
            error: $error === null ? null : new ApiError(
                message: is_string($error['message'] ?? null) ? $error['message'] : 'Test API error',
                type: is_string($error['type'] ?? null) ? $error['type'] : null,
                details: new ApiPayload($error),
                raw: is_string($error['raw'] ?? null) ? $error['raw'] : null,
            ),
            meta: new ApiMeta(
                headers: new ApiHeaders(self::normalizeHeaders($headers)),
                request: self::requestContext($request),
            ),
        );
    }

    /**
     * @param array<string, mixed>|null $request
     */
    private static function requestContext(?array $request): ?ApiRequestContext
    {
        if ($request === null) {
            return null;
        }

        $query = is_array($request['query'] ?? null) ? $request['query'] : [];
        $payload = is_array($request['payload'] ?? null) ? $request['payload'] : [];

        return new ApiRequestContext(
            method: is_string($request['method'] ?? null) ? $request['method'] : 'GET',
            uri: is_string($request['uri'] ?? null) ? $request['uri'] : '',
            query: new ApiPayload($query),
            payload: new ApiPayload($payload),
            attempt: is_int($request['attempt'] ?? null) ? $request['attempt'] : 1,
        );
    }

    /**
     * @param array<string, mixed> $headers
     * @return array<string, list<string>>
     */
    private static function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $name => $values) {
            if (!is_string($name) || !is_array($values)) {
                continue;
            }

            $headerValues = [];

            foreach ($values as $value) {
                if (is_string($value)) {
                    $headerValues[] = $value;
                }
            }

            $normalized[$name] = $headerValues;
        }

        return $normalized;
    }
}
