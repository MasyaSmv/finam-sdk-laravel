<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use DateTimeImmutable;
use MasyaSmv\FinamSdk\Collections\StringCollection;
use MasyaSmv\FinamSdk\Dto\Transport\ApiError;
use MasyaSmv\FinamSdk\Dto\Transport\ApiHeaders;
use MasyaSmv\FinamSdk\Dto\Transport\ApiMeta;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Dto\Transport\ApiRequestContext;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;
use MasyaSmv\FinamSdk\Exceptions\ApiHttpException;
use MasyaSmv\FinamSdk\Exceptions\InvalidResponseException;
use MasyaSmv\FinamSdk\Exceptions\ResponseMappingException;
use MasyaSmv\FinamSdk\Contracts\Session\SessionAccountResolverInterface;
use MasyaSmv\FinamSdk\Session\Mapper\QuoteMapper;
use MasyaSmv\FinamSdk\Session\Service\SessionAccountResolver;
use MasyaSmv\FinamSdk\Session\Service\SessionMarketDataService;
use MasyaSmv\FinamSdk\Session\Service\SessionOperationService;
use MasyaSmv\FinamSdk\Session\Support\ApiResponseDecoder;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;
use MasyaSmv\FinamSdk\Tests\Support\AccountApiStub;
use MasyaSmv\FinamSdk\Tests\Support\ConnectApiStub;
use MasyaSmv\FinamSdk\Tests\Support\MarketApiStub;
use MasyaSmv\FinamSdk\Tests\Support\TestApiResponseFactory;
use ReflectionMethod;

final class SupportCoverageTest extends TestCase
{
    /**
     * @param callable(): mixed $callback
     */
    private function assertThrowsResponseMappingException(callable $callback): void
    {
        try {
            $callback();
            self::fail('Expected ResponseMappingException was not thrown.');
        } catch (ResponseMappingException) {
            self::assertTrue(true);
        }
    }

    /**
     * @param callable(): mixed $callback
     */
    private function assertThrowsInvalidRequestException(callable $callback): void
    {
        try {
            $callback();
            self::fail('Expected InvalidRequestException was not thrown.');
        } catch (\MasyaSmv\FinamSdk\Exceptions\InvalidRequestException) {
            self::assertTrue(true);
        }
    }

    public function testApiPayloadAndHeadersCoverAllReaders(): void
    {
        $payload = new ApiPayload([
            'string' => 'value',
            'bool' => true,
            'int' => '42',
            'object' => ['foo' => 'bar'],
            'list' => [['id' => '1']],
            'string_list' => ['a', 'b'],
            'decimal_object' => ['value' => '1.23'],
            'decimal_string' => '2.34',
            'empty' => '',
        ]);
        $headers = new ApiHeaders([
            'X-Request-Id' => ['', 'req-1'],
            'request-id' => ['req-2'],
        ]);

        $this->assertTrue($payload->has('string'));
        $this->assertSame('value', $payload->string('string'));
        $this->assertTrue($payload->bool('bool'));
        $this->assertSame(42, $payload->int('int'));
        $this->assertSame(['foo' => 'bar'], $payload->object('object')?->toArray());
        $objectList = $payload->objectList('list');
        $this->assertNotNull($objectList);
        $this->assertCount(1, $objectList);
        $this->assertInstanceOf(StringCollection::class, $payload->stringList('string_list'));
        $this->assertSame('1.23', $payload->decimalString('decimal_object'));
        $this->assertSame('2.34', $payload->decimalString('decimal_string'));
        $this->assertSame('value', $payload->firstStringByKeys('empty', 'string'));
        $this->assertNull($payload->string('missing'));
        $this->assertNull($payload->bool('missing'));
        $this->assertNull($payload->int('string'));
        $this->assertNull($payload->object('list'));
        $this->assertNull((new ApiPayload(['list' => [1]]) )->objectList('list'));
        $this->assertNull((new ApiPayload(['string_list' => ['a', 1]]) )->stringList('string_list'));
        $this->assertSame('req-1', $headers->firstValueByNames('x-request-id', 'request-id'));
        $this->assertSame('req-2', $headers->firstValueByNames('missing', 'request-id'));
        $this->assertNull($headers->firstValueByNames('missing'));
        $this->assertNull($payload->firstStringByKeys('missing', 'empty'));
        $this->assertSame(['X-Request-Id' => ['', 'req-1'], 'request-id' => ['req-2']], $headers->toArray());
    }

    public function testApiValueReaderCoversSuccessAndFailureCases(): void
    {
        $reader = new ApiValueReader();
        $payload = new ApiPayload([
            'object' => ['foo' => 'bar'],
            'string' => 'value',
            'bool' => true,
            'int' => '42',
            'dt' => '2026-04-01T10:00:00Z',
            'timestamp' => ['seconds' => '1775037600', 'nanos' => 123000000],
            'decimal' => ['value' => '12.34'],
            'strings' => ['a', 'b'],
            'objects' => [['id' => '1']],
        ]);
        $request = new ApiRequestContext('GET', 'uri', new ApiPayload(), new ApiPayload(), 1);
        $response = new ApiResponse(true, 200, new ApiPayload(), null, new ApiMeta(new ApiHeaders(), $request));

        $this->assertSame(['foo' => 'bar'], $reader->requireObject($payload, 'object')->toArray());
        $this->assertNull($reader->optionalObject($payload, 'missing'));
        $this->assertSame('value', $reader->requireString($payload, 'string'));
        $this->assertNull($reader->optionalString($payload, 'missing'));
        $this->assertTrue($reader->requireBool($payload, 'bool'));
        $this->assertSame(42, $reader->requireInt($payload, 'int', 'ctx'));
        $this->assertNull($reader->optionalInt($payload, 'missing'));
        $this->assertEquals(new DateTimeImmutable('2026-04-01T10:00:00Z'), $reader->parseDateTime('2026-04-01T10:00:00Z', 'dt'));
        $this->assertEquals(new DateTimeImmutable('2026-04-01T10:00:00Z'), $reader->optionalDateTime($payload, 'dt'));
        $this->assertNull($reader->optionalDateTime(new ApiPayload(['dt' => '']), 'dt'));
        $this->assertEquals(new DateTimeImmutable('2026-04-01T10:00:00.123000+00:00'), $reader->requireTimestamp($payload, 'timestamp'));
        $this->assertNull($reader->optionalTimestamp(new ApiPayload(), 'timestamp'));
        $this->assertSame('12.34', $reader->requireDecimal($payload, 'decimal'));
        $this->assertNull($reader->optionalDecimal(new ApiPayload(), 'decimal'));
        $this->assertSame(['a', 'b'], $reader->requireStringList($payload, 'strings')->all());
        $this->assertNull($reader->optionalStringList(new ApiPayload(), 'strings'));
        $this->assertCount(1, $reader->requireObjectList($payload, 'objects'));
        $this->assertSame($request, $reader->requestContext($response));
        $this->assertSame('value', $reader->firstStringByKeys($payload, ['missing', 'string']));
        $this->assertNull($reader->firstHeaderValueByNames(new ApiHeaders(), ['x-request-id']));
        $this->assertSame('req-1', $reader->firstHeaderValueByNames(new ApiHeaders(['x-request-id' => ['req-1']]), ['x-request-id']));

        $this->expectException(ResponseMappingException::class);
        $reader->requireObject(new ApiPayload(), 'object');
    }

    public function testApiValueReaderFailureBranches(): void
    {
        $reader = new ApiValueReader();

        foreach ([
            fn () => $reader->requireString(new ApiPayload(), 'field'),
            fn () => $reader->requireBool(new ApiPayload(), 'field'),
            fn () => $reader->requireInt(new ApiPayload(), 'field', 'ctx'),
            fn () => $reader->parseDateTime('bad-date', 'field'),
            fn () => $reader->requireTimestamp(new ApiPayload(), 'field'),
            fn () => $reader->timestampFromPayload(new ApiPayload(['seconds' => 'x']), 'field'),
            fn () => $reader->timestampFromPayload(new ApiPayload(['seconds' => 1, 'nanos' => -1000]), 'field'),
            fn () => $reader->requireDecimal(new ApiPayload(), 'field'),
            fn () => $reader->requireStringList(new ApiPayload(), 'field'),
            fn () => $reader->requireObjectList(new ApiPayload(), 'field'),
        ] as $callback) {
            $this->assertThrowsResponseMappingException($callback);
        }
    }

    public function testApiResponseDecoderCoversErrorAndMissingDataBranches(): void
    {
        $reader = new ApiValueReader();
        $decoder = new ApiResponseDecoder($reader);

        $errorResponse = new ApiResponse(
            false,
            403,
            null,
            new ApiError('Forbidden', null, new ApiPayload([
                'message' => 'Permission denied',
                'code' => '403',
                'request_id' => 'payload-req',
            ]), 'raw-error'),
            new ApiMeta(
                new ApiHeaders(['x-request-id' => ['header-req']]),
                new ApiRequestContext('GET', 'orders', new ApiPayload(), new ApiPayload(), 1),
            ),
        );

        try {
            $decoder->extractData($errorResponse, 'orders');
            $this->fail('Expected ApiHttpException was not thrown.');
        } catch (ApiHttpException $exception) {
            $this->assertSame('[403] Permission denied', $exception->getMessage());
            $this->assertSame('header-req', $exception->requestId);
            $this->assertSame('403', $exception->finamCode);
            $this->assertSame('Permission denied', $exception->finamMessage);
        }

        $fallbackError = new ApiResponse(
            false,
            500,
            null,
            new ApiError('ignored', null, null, null),
            new ApiMeta(new ApiHeaders(), null),
        );

        try {
            $decoder->extractData($fallbackError, 'usage');
            $this->fail('Expected ApiHttpException was not thrown.');
        } catch (ApiHttpException $exception) {
            $this->assertSame('Finam API request failed for endpoint "usage".', $exception->getMessage());
        }

        $this->expectException(InvalidResponseException::class);
        $decoder->extractData(
            new ApiResponse(true, 200, null, null, new ApiMeta(new ApiHeaders(), null)),
            'missing-data',
        );
    }

    public function testApiResponseDecoderPrivateResolversAreCovered(): void
    {
        $decoder = new ApiResponseDecoder(new ApiValueReader());
        $messageResolver = new ReflectionMethod(ApiResponseDecoder::class, 'resolveApiErrorMessage');
        $messageResolver->setAccessible(true);
        $finamMessageResolver = new ReflectionMethod(ApiResponseDecoder::class, 'resolveFinamMessage');
        $finamMessageResolver->setAccessible(true);
        $finamCodeResolver = new ReflectionMethod(ApiResponseDecoder::class, 'resolveFinamCode');
        $finamCodeResolver->setAccessible(true);
        $requestIdResolver = new ReflectionMethod(ApiResponseDecoder::class, 'resolveRequestId');
        $requestIdResolver->setAccessible(true);

        $payload = new ApiPayload([
            'description' => 'desc',
            'error_code' => 'ERR-1',
            'correlation_id' => 'corr-1',
        ]);

        $this->assertSame('desc', $finamMessageResolver->invoke($decoder, $payload));
        $this->assertNull($finamMessageResolver->invoke($decoder, null));
        $this->assertSame('ERR-1', $finamCodeResolver->invoke($decoder, $payload));
        $this->assertNull($finamCodeResolver->invoke($decoder, null));
        $this->assertSame('[ERR-1] desc', $messageResolver->invoke($decoder, 'orders', $payload));
        $this->assertSame('desc', $messageResolver->invoke($decoder, 'orders', new ApiPayload(['description' => 'desc'])));
        $this->assertSame('corr-1', $requestIdResolver->invoke($decoder, new ApiHeaders(), $payload));
        $this->assertSame('header-1', $requestIdResolver->invoke($decoder, new ApiHeaders(['request-id' => ['header-1']]), $payload));
        $this->assertNull($requestIdResolver->invoke($decoder, new ApiHeaders(), null));
    }

    public function testSessionResolverAndServiceEdgeCases(): void
    {
        $detailsService = new \MasyaSmv\FinamSdk\Session\Service\SessionDetailsService(
            new ConnectApiStub(TestApiResponseFactory::fromArray([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'created_at' => '2026-03-31T10:00:00+03:00',
                    'expires_at' => '2026-03-31T20:00:00+03:00',
                    'account_ids' => [],
                    'readonly' => false,
                ],
                'error' => null,
                'meta' => [],
            ])),
            new ApiResponseDecoder(new ApiValueReader()),
            new \MasyaSmv\FinamSdk\Session\Mapper\SessionDetailsMapper(new ApiValueReader()),
        );

        $resolver = new SessionAccountResolver($detailsService);

        $this->expectException(\MasyaSmv\FinamSdk\Exceptions\AccountResolutionException::class);
        $resolver->resolveDefaultAccountId();
    }

    public function testSessionMarketAndOperationServicesValidateInputBranchesAndQuoteFallback(): void
    {
        $reader = new ApiValueReader();
        $decoder = new ApiResponseDecoder($reader);
        $marketService = new SessionMarketDataService(
            new MarketApiStub(
                quotesResponse: TestApiResponseFactory::fromArray([
                    'ok' => true,
                    'status' => 200,
                    'data' => [
                        'symbol' => 'SBER@MISX',
                        'quote' => [
                            'last' => ['value' => '250.10'],
                        ],
                    ],
                    'error' => null,
                    'meta' => [],
                ]),
                candlesResponse: TestApiResponseFactory::fromArray([
                    'ok' => true,
                    'status' => 200,
                    'data' => ['symbol' => 'SBER@MISX', 'bars' => []],
                    'error' => null,
                    'meta' => [],
                ]),
                orderbookResponse: TestApiResponseFactory::fromArray([
                    'ok' => true,
                    'status' => 200,
                    'data' => ['symbol' => 'SBER@MISX', 'orderbook' => ['rows' => []]],
                    'error' => null,
                    'meta' => [],
                ]),
                tradesResponse: TestApiResponseFactory::fromArray([
                    'ok' => true,
                    'status' => 200,
                    'data' => ['symbol' => 'SBER@MISX', 'trades' => []],
                    'error' => null,
                    'meta' => [],
                ]),
            ),
            $decoder,
            new QuoteMapper($reader),
            new \MasyaSmv\FinamSdk\Session\Mapper\CandleMapper($reader),
            new \MasyaSmv\FinamSdk\Session\Mapper\OrderBookMapper($reader),
            new \MasyaSmv\FinamSdk\Session\Mapper\TradeMapper($reader),
        );

        /** @var \MasyaSmv\FinamSdk\Dto\Market\QuoteDto|null $firstQuote */
        $firstQuote = $marketService->getLatestQuotes(['SBER@MISX'])->first();
        $this->assertSame('SBER@MISX', $firstQuote?->symbol());

        foreach ([
            fn () => $marketService->getLatestQuotes([]),
            fn () => $marketService->getLatestQuotes(['']),
            fn () => $marketService->getOrderBook(''),
            fn () => $marketService->getLatestTrades(''),
        ] as $callback) {
            $this->assertThrowsInvalidRequestException($callback);
        }

        $resolver = $this->createStub(SessionAccountResolverInterface::class);
        $resolver->method('resolveDefaultAccountId')->willReturn('ACC-1');

        $operationService = new SessionOperationService(
            new AccountApiStub(TestApiResponseFactory::fromArray([
                'ok' => true,
                'status' => 200,
                'data' => ['transactions' => []],
                'error' => null,
                'meta' => [],
            ])),
            $resolver,
            $decoder,
            new \MasyaSmv\FinamSdk\Session\Mapper\OperationMapper($reader),
        );

        $this->expectException(\MasyaSmv\FinamSdk\Exceptions\InvalidRequestException::class);
        $operationService->getOperationsByDate(new DateTimeImmutable('2026-04-02'), new DateTimeImmutable('2026-04-01'));
    }
}
