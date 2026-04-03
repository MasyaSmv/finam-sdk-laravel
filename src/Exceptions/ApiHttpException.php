<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Exceptions;

use MasyaSmv\FinamSdk\Dto\Transport\ApiHeaders;
use MasyaSmv\FinamSdk\Dto\Transport\ApiDiagnosticContext;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Dto\Transport\ApiRequestContext;

/**
 * HTTP-ошибка от API (4xx/5xx), когда включен режим throwOnHttpError.
 * Хранит статус, заголовки и распарсенный payload ошибки (если был JSON).
 */
final class ApiHttpException extends FinamSdkException
{
    public ApiDiagnosticContext $context;

    public function __construct(
        string $message,
        public int $httpStatus,
        public string $endpoint = '',
        public ?string $requestId = null,
        public ?string $finamCode = null,
        public ?string $finamMessage = null,
        public ?ApiRequestContext $requestContext = null,
        public ?ApiHeaders $headers = null,
        public ?ApiPayload $errorPayload = null,
        public ?string $rawBody = null,
    ) {
        parent::__construct($message, $httpStatus);

        $this->context = new ApiDiagnosticContext(
            endpoint: $this->endpoint,
            request: $this->requestContext,
            headers: $this->headers,
            requestId: $this->requestId,
            errorPayload: $this->errorPayload,
            rawBody: $this->rawBody,
        );
    }
}
