<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Support;

use MasyaSmv\FinamSdk\Contracts\Session\ApiResponseDecoderInterface;
use MasyaSmv\FinamSdk\Exceptions\ApiHttpException;
use MasyaSmv\FinamSdk\Exceptions\InvalidResponseException;

/**
 * @phpstan-import-type ApiMap from ApiValueReader
 * @phpstan-import-type ApiResponse from ApiValueReader
 * @psalm-import-type ApiMap from ApiValueReader
 * @psalm-import-type ApiResponse from ApiValueReader
 */
final class ApiResponseDecoder implements ApiResponseDecoderInterface
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    /**
     * @param ApiResponse $response
     *
     * @return ApiMap
     */
    public function extractData(array $response, string $endpoint): array
    {
        $ok = $response['ok'] ?? null;

        if ($ok !== true) {
            $status = $this->reader->optionalInt($response, 'status') ?? 0;
            $headers = $this->reader->headerMap($response['meta']['headers'] ?? null);
            /** @var ApiMap|null $errorPayload */
            $errorPayload = is_array($response['error'] ?? null) ? $response['error'] : null;
            $finamMessage = $this->resolveFinamMessage($errorPayload);
            $finamCode = $this->resolveFinamCode($errorPayload);
            $message = $this->resolveApiErrorMessage($endpoint, $errorPayload);

            throw new ApiHttpException(
                message: $message,
                httpStatus: $status,
                endpoint: $endpoint,
                requestId: $this->resolveRequestId($headers, $errorPayload),
                finamCode: $finamCode,
                finamMessage: $finamMessage,
                requestContext: $this->reader->requestContext($response),
                headers: $headers,
                errorPayload: $errorPayload,
                rawBody: $this->reader->optionalString($errorPayload ?? [], 'raw'),
            );
        }

        $data = $response['data'] ?? null;

        if (!is_array($data)) {
            throw new InvalidResponseException(
                sprintf('Response data for endpoint "%s" must be an object.', $endpoint),
            );
        }

        /** @var ApiMap $data */
        return $data;
    }

    /**
     * @param ApiMap|null $errorPayload
     */
    private function resolveApiErrorMessage(string $endpoint, ?array $errorPayload): string
    {
        $message = $this->resolveFinamMessage($errorPayload);

        if ($message !== null) {
            $code = $this->resolveFinamCode($errorPayload);

            if ($code !== null && $code !== '') {
                return sprintf('[%s] %s', $code, $message);
            }

            return $message;
        }

        return sprintf('Finam API request failed for endpoint "%s".', $endpoint);
    }

    /**
     * @param ApiMap|null $errorPayload
     */
    private function resolveFinamMessage(?array $errorPayload): ?string
    {
        if ($errorPayload === null) {
            return null;
        }

        return $this->reader->firstStringByKeys($errorPayload, ['message', 'error', 'description', 'detail']);
    }

    /**
     * @param ApiMap|null $errorPayload
     */
    private function resolveFinamCode(?array $errorPayload): ?string
    {
        if ($errorPayload === null) {
            return null;
        }

        return $this->reader->firstStringByKeys($errorPayload, ['code', 'error_code']);
    }

    /**
     * @param array<string, list<string>> $headers
     * @param ApiMap|null $errorPayload
     */
    private function resolveRequestId(array $headers, ?array $errorPayload): ?string
    {
        $requestId = $this->reader->firstHeaderValueByNames($headers, ['x-request-id', 'x-correlation-id', 'request-id']);

        if ($requestId !== null) {
            return $requestId;
        }

        if ($errorPayload === null) {
            return null;
        }

        return $this->reader->firstStringByKeys($errorPayload, ['request_id', 'correlation_id']);
    }
}
