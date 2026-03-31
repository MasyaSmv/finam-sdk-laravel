<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts;

use MasyaSmv\FinamSdk\Dto\Auth\IssuedTokenDto;

interface AuthServiceInterface
{
    public function issueToken(string $secret): IssuedTokenDto;
}
