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

final class SessionDetailsTest extends TestCase
{
    public function testSessionDetailsAreMappedToDto(): void
    {
        $session = FinamSession::fromApis(
            connectApi: new ConnectApiStub([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'created_at' => '2026-03-31T10:00:00+03:00',
                    'expires_at' => '2026-03-31T20:00:00+03:00',
                    'account_ids' => ['ACC-1'],
                    'readonly' => false,
                ],
                'error' => null,
                'meta' => [],
            ]),
            accountApi: new AccountApiStub([]),
            orderApi: new OrderApiStub([], [], []),
            instrumentApi: new InstrumentApiStub([], []),
            marketApi: new MarketApiStub([], []),
        );

        $details = $session->sessionDetails();

        $this->assertSame(['ACC-1'], $details->accountIds());
        $this->assertFalse($details->readonly());
        $this->assertSame('2026-03-31T10:00:00+03:00', $details->createdAt()->format(DATE_ATOM));
        $this->assertSame('2026-03-31T20:00:00+03:00', $details->expiresAt()->format(DATE_ATOM));
    }

    public function testApiErrorUsesFinamMessageAndCode(): void
    {
        $session = FinamSession::fromApis(
            connectApi: new ConnectApiStub([
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
            ]),
            accountApi: new AccountApiStub([]),
            orderApi: new OrderApiStub([], [], []),
            instrumentApi: new InstrumentApiStub([], []),
            marketApi: new MarketApiStub([], []),
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
            $this->assertSame('POST', $exception->requestContext['method'] ?? null);
        }
    }
}
