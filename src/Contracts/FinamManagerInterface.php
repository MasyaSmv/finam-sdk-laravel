<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts;

use MasyaSmv\FinamSdk\Client\FinamClient;

interface FinamManagerInterface
{
    public function connect(string $token): FinamSessionInterface;

    public function client(?string $token = null): FinamClient;
}
