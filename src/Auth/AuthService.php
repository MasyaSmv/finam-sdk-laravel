<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Auth;

use MasyaSmv\FinamSdk\Contracts\AuthServiceInterface;
use MasyaSmv\FinamSdk\Contracts\Api\AuthApiInterface;
use MasyaSmv\FinamSdk\Contracts\Session\ApiResponseDecoderInterface;
use MasyaSmv\FinamSdk\Dto\Auth\AuthRequest;
use MasyaSmv\FinamSdk\Dto\Auth\IssuedTokenDto;
use MasyaSmv\FinamSdk\Session\Mapper\IssuedTokenMapper;

final class AuthService implements AuthServiceInterface
{
    public function __construct(
        private AuthApiInterface $authApi,
        private ApiResponseDecoderInterface $decoder,
        private IssuedTokenMapper $mapper,
    ) {
    }

    public function issueToken(string $secret): IssuedTokenDto
    {
        $response = $this->authApi->issueToken(new AuthRequest($secret));
        $data = $this->decoder->extractData($response, 'sessions');

        return $this->mapper->map($data);
    }
}
