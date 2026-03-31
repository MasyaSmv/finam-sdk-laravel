<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Auth;

final class IssuedTokenDto
{
    public function __construct(private string $token)
    {
    }

    public function token(): string
    {
        return $this->token;
    }
}
