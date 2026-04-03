<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts;

use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Dto\Auth\IssuedTokenDto;

interface FinamManagerInterface
{
    public function issueToken(string $secret): IssuedTokenDto;

    public function connectSecret(string $secret): FinamSessionInterface;

    public function connect(string $token): FinamSessionInterface;

    public function client(string $token): FinamClient;
}
