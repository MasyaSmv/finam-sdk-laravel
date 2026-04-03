<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Exceptions;

use MasyaSmv\FinamSdk\Dto\Transport\ApiDiagnosticContext;

/**
 * Сервер ответил, но формат не соответствует ожиданиям SDK.
 * Например: HTML вместо JSON, битый JSON, пустое тело.
 */
final class InvalidResponseException extends FinamSdkException
{
    public function __construct(
        string $message,
        public int $httpStatus = 0,
        public ?string $rawBody = null,
        public ?ApiDiagnosticContext $context = null,
    ) {
        parent::__construct($message);
    }
}
