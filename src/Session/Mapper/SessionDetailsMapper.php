<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Collections\StringCollection;
use MasyaSmv\FinamSdk\Dto\Connect\SessionDetailsDto;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;
final class SessionDetailsMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    public function map(ApiPayload $data): SessionDetailsDto
    {
        return new SessionDetailsDto(
            createdAt: $this->reader->parseDateTime($this->reader->requireString($data, 'created_at'), 'created_at'),
            expiresAt: $this->reader->parseDateTime($this->reader->requireString($data, 'expires_at'), 'expires_at'),
            mdPermissions: $this->reader->optionalStringList($data, 'md_permissions') ?? new StringCollection(),
            accountIds: $this->reader->requireStringList($data, 'account_ids'),
            readonly: $this->reader->requireBool($data, 'readonly'),
        );
    }
}
