<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Transport;

final class ApiError
{
    public function __construct(
        private string $message,
        private ?string $type = null,
        private ?ApiPayload $details = null,
        private ?string $raw = null,
    ) {
    }

    public function message(): string
    {
        return $this->message;
    }

    public function type(): ?string
    {
        return $this->type;
    }

    public function details(): ?ApiPayload
    {
        return $this->details;
    }

    public function raw(): ?string
    {
        return $this->raw;
    }
}
