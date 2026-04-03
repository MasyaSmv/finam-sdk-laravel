<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Api\Auth;

use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Contracts\Api\AuthApiInterface;
use MasyaSmv\FinamSdk\Dto\Auth\AuthRequest;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

final class AuthApi implements AuthApiInterface
{
    public function __construct(private FinamClient $client)
    {
    }

    public function issueToken(AuthRequest $request): ApiResponse
    {
        return $this->client->post('/sessions', $request->toPayload());
    }
}
