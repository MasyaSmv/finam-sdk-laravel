<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Dto\Auth\IssuedTokenDto;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

final class IssuedTokenMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    public function map(ApiPayload $data): IssuedTokenDto
    {
        return new IssuedTokenDto(
            token: $this->reader->requireString($data, 'token'),
        );
    }
}
