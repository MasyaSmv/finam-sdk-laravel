<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Transport;

final class ApiResponse
{
    public function __construct(
        private bool $ok,
        private int $status,
        private ?ApiPayload $data,
        private ?ApiError $error,
        private ApiMeta $meta,
    ) {
    }

    public function ok(): bool
    {
        return $this->ok;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function data(): ?ApiPayload
    {
        return $this->data;
    }

    public function error(): ?ApiError
    {
        return $this->error;
    }

    public function meta(): ApiMeta
    {
        return $this->meta;
    }
}
