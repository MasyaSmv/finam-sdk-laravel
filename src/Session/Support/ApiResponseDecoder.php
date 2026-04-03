<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Support;

use MasyaSmv\FinamSdk\Contracts\Session\ApiResponseDecoderInterface;
use MasyaSmv\FinamSdk\Dto\Transport\ApiHeaders;
use MasyaSmv\FinamSdk\Dto\Transport\ApiDiagnosticContext;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;
use MasyaSmv\FinamSdk\Exceptions\ApiHttpException;
use MasyaSmv\FinamSdk\Exceptions\InvalidResponseException;

final class ApiResponseDecoder implements ApiResponseDecoderInterface
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    public function extractData(ApiResponse $response, string $endpoint): ApiPayload
    {
        if (!$response->ok()) {
            $errorPayload = $response->error()?->details();
            $finamMessage = $this->resolveFinamMessage($errorPayload);
            $finamCode = $this->resolveFinamCode($errorPayload);
            $message = $this->resolveApiErrorMessage($endpoint, $errorPayload);

            throw new ApiHttpException(
                message: $message,
                httpStatus: $response->status(),
                endpoint: $endpoint,
                requestId: $this->resolveRequestId($response->meta()->headers(), $errorPayload),
                finamCode: $finamCode,
                finamMessage: $finamMessage,
                requestContext: $this->reader->requestContext($response),
                headers: $response->meta()->headers(),
                errorPayload: $errorPayload,
                rawBody: $response->error()?->raw(),
            );
        }

        $data = $response->data();

        if ($data === null) {
            throw new InvalidResponseException(
                sprintf('Response data for endpoint "%s" must be an object.', $endpoint),
                context: new ApiDiagnosticContext(
                    endpoint: $endpoint,
                    request: $this->reader->requestContext($response),
                    headers: $response->meta()->headers(),
                    rawBody: $response->error()?->raw(),
                ),
            );
        }

        return $data;
    }

    private function resolveApiErrorMessage(string $endpoint, ?ApiPayload $errorPayload): string
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

    private function resolveFinamMessage(?ApiPayload $errorPayload): ?string
    {
        if ($errorPayload === null) {
            return null;
        }

        return $this->reader->firstStringByKeys($errorPayload, ['message', 'error', 'description', 'detail']);
    }

    private function resolveFinamCode(?ApiPayload $errorPayload): ?string
    {
        if ($errorPayload === null) {
            return null;
        }

        return $this->reader->firstStringByKeys($errorPayload, ['code', 'error_code']);
    }

    private function resolveRequestId(ApiHeaders $headers, ?ApiPayload $errorPayload): ?string
    {
        $requestId = $this->reader->firstHeaderValueByNames(
            $headers,
            ['x-request-id', 'x-correlation-id', 'request-id'],
        );

        if ($requestId !== null) {
            return $requestId;
        }

        if ($errorPayload === null) {
            return null;
        }

        return $this->reader->firstStringByKeys($errorPayload, ['request_id', 'correlation_id']);
    }
}
