<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Exceptions;

use MasyaSmv\FinamSdk\Dto\Transport\ApiDiagnosticContext;
use Throwable;

/**
 * Ошибка, возникающая при неуспешных попытках выполнить HTTP-запрос к API.
 */
class ApiRequestFailedException extends FinamSdkException
{
    public function __construct(
        string $message,
        public ?ApiDiagnosticContext $context = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, previous: $previous);
    }
}
