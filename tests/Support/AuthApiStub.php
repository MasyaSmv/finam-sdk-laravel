<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests\Support;

use MasyaSmv\FinamSdk\Contracts\Api\AuthApiInterface;
use MasyaSmv\FinamSdk\Dto\Auth\AuthRequest;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

final class AuthApiStub implements AuthApiInterface
{
    public function __construct(private ApiResponse $response)
    {
    }

    public function issueToken(AuthRequest $request): ApiResponse
    {
        return $this->response;
    }
}
