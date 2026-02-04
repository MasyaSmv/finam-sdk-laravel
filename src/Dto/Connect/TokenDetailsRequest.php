<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Connect;

final class TokenDetailsRequest
{
    public function __construct(private ?string $token = null)
    {
    }

    public function token(): ?string
    {
        return $this->token;
    }
}
