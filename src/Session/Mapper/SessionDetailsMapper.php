<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Dto\Connect\SessionDetailsDto;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

/**
 * @phpstan-import-type ApiMap from ApiValueReader
 * @psalm-import-type ApiMap from ApiValueReader
 */
final class SessionDetailsMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    /**
     * @param ApiMap $data
     */
    public function map(array $data): SessionDetailsDto
    {
        return new SessionDetailsDto(
            createdAt: $this->reader->parseDateTime($this->reader->requireString($data, 'created_at'), 'created_at'),
            expiresAt: $this->reader->parseDateTime($this->reader->requireString($data, 'expires_at'), 'expires_at'),
            accountIds: $this->reader->stringList($data['account_ids'] ?? null, 'account_ids'),
            readonly: $this->reader->requireBool($data, 'readonly'),
        );
    }
}
