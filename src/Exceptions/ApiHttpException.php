<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Exceptions;

/**
 * HTTP-ошибка от API (4xx/5xx), когда включен режим throwOnHttpError.
 * Хранит статус, заголовки и распарсенный payload ошибки (если был JSON).
 */
final class ApiHttpException extends FinamSdkException
{
    /**
     * @param array<string, array<int, string>> $headers
     * @param array<string, mixed>|null $errorPayload
     */
    public function __construct(
        string $message,
        public int $httpStatus,
        public array $headers = [],
        public ?array $errorPayload = null,
        public ?string $rawBody = null,
    ) {
        parent::__construct($message, $httpStatus);
    }
}
