<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Auth;

use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class AuthRequest
{
    public function __construct(private string $secret)
    {
        if ($this->secret === '') {
            throw new InvalidRequestException('Secret must not be empty.');
        }
    }

    public function secret(): string
    {
        return $this->secret;
    }

    /**
     * @return array{secret: string}
     */
    public function toPayload(): array
    {
        return [
            'secret' => $this->secret,
        ];
    }
}
