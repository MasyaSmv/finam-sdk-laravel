<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Api;

interface ConnectApiInterface
{
    /**
     * @return array<string, mixed>
     */
    public function tokenDetails(): array;
}
