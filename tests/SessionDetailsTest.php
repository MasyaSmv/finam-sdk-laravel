<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use MasyaSmv\FinamSdk\Exceptions\ApiHttpException;
use MasyaSmv\FinamSdk\Session\FinamSession;
use MasyaSmv\FinamSdk\Tests\Support\AccountApiStub;
use MasyaSmv\FinamSdk\Tests\Support\ConnectApiStub;
use MasyaSmv\FinamSdk\Tests\Support\InstrumentApiStub;
use MasyaSmv\FinamSdk\Tests\Support\MarketApiStub;
use MasyaSmv\FinamSdk\Tests\Support\OrderApiStub;
use MasyaSmv\FinamSdk\Tests\Support\TestApiResponseFactory;

final class SessionDetailsTest extends TestCase
{
    public function testSessionDetailsAreMappedToDto(): void
    {
        $session = FinamSession::fromApis(
            connectApi: new ConnectApiStub(TestApiResponseFactory::fromArray([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'created_at' => '2026-03-31T10:00:00+03:00',
                    'expires_at' => '2026-03-31T20:00:00+03:00',
                    'md_permissions' => ['QUOTE', 'ORDER_BOOK'],
                    'account_ids' => ['ACC-1'],
                    'readonly' => false,
                ],
                'error' => null,
                'meta' => [],
            ])),
            accountApi: new AccountApiStub(TestApiResponseFactory::fromArray([])),
            orderApi: new OrderApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
            instrumentApi: new InstrumentApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
            marketApi: new MarketApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
        );

        $details = $session->sessionDetails();

        $this->assertSame(['ACC-1'], $details->accountIds()->strings());
        $this->assertSame(['QUOTE', 'ORDER_BOOK'], $details->mdPermissions()->strings());
        $this->assertFalse($details->readonly());
        $this->assertSame('2026-03-31T10:00:00+03:00', $details->createdAt()->format(DATE_ATOM));
        $this->assertSame('2026-03-31T20:00:00+03:00', $details->expiresAt()->format(DATE_ATOM));
    }

    public function testApiErrorUsesFinamMessageAndCode(): void
    {
        $session = FinamSession::fromApis(
            connectApi: new ConnectApiStub(TestApiResponseFactory::fromArray([
                'ok' => false,
                'status' => 403,
                'data' => null,
                'error' => [
                    'message' => 'Token is invalid',
                    'code' => 'AUTH-403',
                    'request_id' => 'req-403',
                ],
                'meta' => [
                    'headers' => [
                        'x-request-id' => ['req-403'],
                    ],
                    'request' => [
                        'method' => 'POST',
                        'uri' => 'sessions/details',
                        'attempt' => 1,
                    ],
                ],
            ])),
            accountApi: new AccountApiStub(TestApiResponseFactory::fromArray([])),
            orderApi: new OrderApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
            instrumentApi: new InstrumentApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
            marketApi: new MarketApiStub(
                TestApiResponseFactory::fromArray([]),
                TestApiResponseFactory::fromArray([]),
            ),
        );

        try {
            $session->sessionDetails();
            $this->fail('Expected ApiHttpException was not thrown.');
        } catch (ApiHttpException $exception) {
            $this->assertSame('[AUTH-403] Token is invalid', $exception->getMessage());
            $this->assertSame('AUTH-403', $exception->finamCode);
            $this->assertSame('Token is invalid', $exception->finamMessage);
            $this->assertSame('req-403', $exception->requestId);
            $this->assertSame('sessions/details', $exception->endpoint);
            $this->assertSame('POST', $exception->requestContext?->method());
        }
    }
}
