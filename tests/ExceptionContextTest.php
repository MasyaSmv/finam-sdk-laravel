<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use MasyaSmv\FinamSdk\Dto\Transport\ApiDiagnosticContext;
use MasyaSmv\FinamSdk\Dto\Transport\ApiHeaders;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Dto\Transport\ApiRequestContext;
use MasyaSmv\FinamSdk\Exceptions\ApiHttpException;
use MasyaSmv\FinamSdk\Exceptions\ApiRequestFailedException;
use MasyaSmv\FinamSdk\Exceptions\InvalidResponseException;

final class ExceptionContextTest extends TestCase
{
    public function testApiHttpExceptionBuildsDiagnosticContext(): void
    {
        $requestContext = new ApiRequestContext(
            method: 'GET',
            uri: 'assets',
            query: new ApiPayload([]),
            payload: new ApiPayload([]),
            attempt: 1,
        );
        $headers = new ApiHeaders([
            'x-request-id' => ['request-1'],
        ]);
        $payload = new ApiPayload([
            'code' => 'bad_request',
        ]);

        $exception = new ApiHttpException(
            message: 'bad request',
            httpStatus: 400,
            endpoint: 'assets',
            requestId: 'request-1',
            finamCode: 'bad_request',
            finamMessage: 'bad request',
            requestContext: $requestContext,
            headers: $headers,
            errorPayload: $payload,
            rawBody: '{"code":"bad_request"}',
        );

        $this->assertSame('assets', $exception->context->endpoint());
        $this->assertSame('request-1', $exception->context->requestId());
        $this->assertSame($requestContext, $exception->context->request());
        $this->assertSame($headers, $exception->context->headers());
        $this->assertSame($payload, $exception->context->errorPayload());
    }

    public function testApiRequestFailedExceptionCanCarryDiagnosticContext(): void
    {
        $context = new ApiDiagnosticContext(
            endpoint: 'accounts/orders',
            request: new ApiRequestContext(
                method: 'POST',
                uri: 'accounts/orders',
                query: new ApiPayload([]),
                payload: new ApiPayload([
                    'symbol' => 'SBER@MISX',
                ]),
                attempt: 2,
            ),
        );

        $exception = new ApiRequestFailedException(
            'network failure',
            context: $context,
        );

        $this->assertSame($context, $exception->context);
        $this->assertNotNull($exception->context);
        $this->assertSame('accounts/orders', $exception->context->endpoint());
    }

    public function testInvalidResponseExceptionCanCarryDiagnosticContext(): void
    {
        $context = new ApiDiagnosticContext(
            endpoint: 'assets',
            rawBody: '<html>broken</html>',
        );

        $exception = new InvalidResponseException(
            'invalid json',
            httpStatus: 200,
            rawBody: '<html>broken</html>',
            context: $context,
        );

        $this->assertSame($context, $exception->context);
        $this->assertNotNull($exception->context);
        $this->assertSame('<html>broken</html>', $exception->context->rawBody());
    }
}
