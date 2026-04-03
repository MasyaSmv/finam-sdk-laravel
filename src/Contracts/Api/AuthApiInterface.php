<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Api;

use MasyaSmv\FinamSdk\Dto\Auth\AuthRequest;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

interface AuthApiInterface
{
    public function issueToken(AuthRequest $request): ApiResponse;
}
