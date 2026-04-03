<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use MasyaSmv\FinamSdk\Auth\AuthService;
use MasyaSmv\FinamSdk\Contracts\AuthServiceInterface;
use MasyaSmv\FinamSdk\Exceptions\ApiHttpException;
use MasyaSmv\FinamSdk\Session\Mapper\IssuedTokenMapper;
use MasyaSmv\FinamSdk\Session\Support\ApiResponseDecoder;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;
use MasyaSmv\FinamSdk\Tests\Support\AuthApiStub;
use MasyaSmv\FinamSdk\Tests\Support\TestApiResponseFactory;

final class AuthServiceTest extends TestCase
{
    public function testIssueTokenReturnsTypedDto(): void
    {
        $reader = new ApiValueReader();
        $service = new AuthService(
            authApi: new AuthApiStub(TestApiResponseFactory::fromArray([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'token' => 'jwt-token-value',
                ],
                'error' => null,
                'meta' => [],
            ])),
            decoder: new ApiResponseDecoder($reader),
            mapper: new IssuedTokenMapper($reader),
        );

        $issuedToken = $service->issueToken('secret-key');

        $this->assertSame('jwt-token-value', $issuedToken->token());
    }

    public function testIssueTokenUsesFinamErrorMessageAndCode(): void
    {
        $reader = new ApiValueReader();
        $service = new AuthService(
            authApi: new AuthApiStub(TestApiResponseFactory::fromArray([
                'ok' => false,
                'status' => 403,
                'data' => null,
                'error' => [
                    'message' => 'Secret is invalid',
                    'code' => 'AUTH-403',
                    'request_id' => 'req-auth-403',
                ],
                'meta' => [
                    'headers' => [
                        'x-request-id' => ['req-auth-403'],
                    ],
                    'request' => [
                        'method' => 'POST',
                        'uri' => 'sessions',
                        'attempt' => 1,
                    ],
                ],
            ])),
            decoder: new ApiResponseDecoder($reader),
            mapper: new IssuedTokenMapper($reader),
        );

        try {
            $service->issueToken('invalid-secret');
            $this->fail('Expected ApiHttpException was not thrown.');
        } catch (ApiHttpException $exception) {
            $this->assertSame('[AUTH-403] Secret is invalid', $exception->getMessage());
            $this->assertSame('AUTH-403', $exception->finamCode);
            $this->assertSame('Secret is invalid', $exception->finamMessage);
            $this->assertSame('req-auth-403', $exception->requestId);
            $this->assertSame('sessions', $exception->endpoint);
        }
    }

    public function testAuthServiceBindingIsResolved(): void
    {
        $service = $this->app->make(AuthServiceInterface::class);

        $this->assertInstanceOf(AuthServiceInterface::class, $service);
    }
}
