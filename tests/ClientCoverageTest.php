<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use MasyaSmv\FinamSdk\Api\Account\AccountApi;
use MasyaSmv\FinamSdk\Api\Auth\AuthApi;
use MasyaSmv\FinamSdk\Api\Connect\ConnectApi;
use MasyaSmv\FinamSdk\Api\Instrument\InstrumentApi;
use MasyaSmv\FinamSdk\Api\Market\MarketApi;
use MasyaSmv\FinamSdk\Api\Order\OrderApi;
use MasyaSmv\FinamSdk\Api\Reports\ReportsApi;
use MasyaSmv\FinamSdk\Api\UsageMetrics\UsageMetricsApi;
use MasyaSmv\FinamSdk\Auth\StaticTokenProvider;
use MasyaSmv\FinamSdk\Auth\TokenProviderInterface;
use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Client\FinamClientFactory;
use MasyaSmv\FinamSdk\Contracts\AuthServiceInterface;
use MasyaSmv\FinamSdk\Dto\Auth\IssuedTokenDto;
use MasyaSmv\FinamSdk\Dto\Config\FinamConfig;
use MasyaSmv\FinamSdk\Dto\Config\FinamHttpConfig;
use MasyaSmv\FinamSdk\Exceptions\ApiRequestFailedException;
use MasyaSmv\FinamSdk\Exceptions\InvalidResponseException;
use MasyaSmv\FinamSdk\FinamManager;
use MasyaSmv\FinamSdk\Tests\Support\HttpClientTestHelper;
use ReflectionMethod;
use ReflectionProperty;

final class ClientCoverageTest extends TestCase
{
    use HttpClientTestHelper;

    public function testClientConvenienceConstructorsAndResourceAccessors(): void
    {
        $client = FinamClient::make('token-1', 'https://example.test');
        $other = FinamClient::connectToken('token-2', 'https://example.test');

        $this->assertSame('token-1', $client->getAccessToken());
        $this->assertSame('token-2', $other->getAccessToken());
        $this->assertInstanceOf(ConnectApi::class, $client->connect());
        $this->assertInstanceOf(AuthApi::class, $client->auth());
        $this->assertInstanceOf(AccountApi::class, $client->account());
        $this->assertInstanceOf(InstrumentApi::class, $client->instrument());
        $this->assertInstanceOf(OrderApi::class, $client->order());
        $this->assertInstanceOf(MarketApi::class, $client->market());
        $this->assertInstanceOf(UsageMetricsApi::class, $client->usageMetrics());
        $this->assertInstanceOf(ReportsApi::class, $client->reports());
        $this->assertSame($client->connect(), $client->connect());
    }

    public function testClientHandlesSuccessfulJsonScalarAndHttpErrors(): void
    {
        $history = [];
        $client = $this->makeClientWithQueue([
            new Response(200, ['X-Request-Id' => 'req-1'], '"ok"'),
            new Response(404, ['X-Request-Id' => 'req-2'], '{"message":"missing"}'),
            new Response(500, [], '<html>oops</html>'),
        ], $history);

        $success = $client->get('/ping');
        $notFound = $client->get('/missing');
        $server = $client->get('/broken');
        $notFoundError = $notFound->error();
        $serverError = $server->error();

        $this->assertTrue($success->ok());
        $this->assertSame(['value' => 'ok'], $success->data()?->toArray());
        $this->assertSame('Bearer runtime-token', $history[0]['request']->getHeaderLine('Authorization'));
        $this->assertFalse($notFound->ok());
        $this->assertNotNull($notFoundError);
        $this->assertSame('missing', $notFoundError->message());
        $this->assertSame('not_found', $notFoundError->type());
        $this->assertFalse($server->ok());
        $this->assertNotNull($serverError);
        $this->assertSame('Response is not valid JSON: Syntax error', $serverError->message());
        $this->assertSame('invalid_json', $serverError->type());
        $this->assertSame('<html>oops</html>', $serverError->raw());
    }

    public function testClientThrowsForInvalidJsonOnSuccessfulStatusAndFailedTransport(): void
    {
        $client = $this->makeClientWithQueue([
            new Response(200, [], '{invalid'),
        ]);

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage('Response is not valid JSON');

        $client->get('/bad-json');
    }

    public function testClientRetriesThenThrowsTransportException(): void
    {
        $client = $this->makeClientWithQueue([
            new ConnectException('timeout-1', new Request('GET', '/retry')),
            new ConnectException('timeout-2', new Request('GET', '/retry')),
        ]);

        $reflection = new \ReflectionClass(FinamClient::class);
        foreach (['retries' => 1, 'retryDelayMs' => 0] as $propertyName => $value) {
            $property = $reflection->getProperty($propertyName);
            $property->setAccessible(true);
            $property->setValue($client, $value);
        }

        $this->expectException(ApiRequestFailedException::class);
        $this->expectExceptionMessage('Finam API request failed after 2 attempt(s)');

        $client->get('/retry');
    }

    public function testClientOmitsAuthorizationHeaderWhenCustomProviderReturnsEmptyString(): void
    {
        $history = [];
        $provider = $this->createStub(TokenProviderInterface::class);
        $provider->method('getToken')->willReturn('');

        $client = $this->makeClientWithQueue([new Response(200, [], '{}')], $history, $provider);
        $client->get('/no-auth');

        $this->assertFalse($history[0]['request']->hasHeader('Authorization'));
    }

    public function testClientPrivateResourceMethodRejectsUnknownClass(): void
    {
        $client = new FinamClient('token');
        $method = new ReflectionMethod(FinamClient::class, 'resource');
        $method->setAccessible(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('API resource class does not exist');

        $method->invoke($client, 'Unknown\\Api');
    }

    public function testClientPrivateHelpersCoverErrorResolutionAndMetadata(): void
    {
        $client = new FinamClient('token');

        $guessErrorType = new ReflectionMethod(FinamClient::class, 'guessErrorType');
        $guessErrorType->setAccessible(true);
        $resolveErrorMessage = new ReflectionMethod(FinamClient::class, 'resolveErrorMessage');
        $resolveErrorMessage->setAccessible(true);
        $requestContext = new ReflectionMethod(FinamClient::class, 'requestContext');
        $requestContext->setAccessible(true);
        $meta = new ReflectionMethod(FinamClient::class, 'meta');
        $meta->setAccessible(true);
        $withAuthHeader = new ReflectionMethod(FinamClient::class, 'withAuthHeader');
        $withAuthHeader->setAccessible(true);

        $this->assertSame('auth', $guessErrorType->invoke($client, 401, null));
        $this->assertSame('auth', $guessErrorType->invoke($client, 403, null));
        $this->assertSame('not_found', $guessErrorType->invoke($client, 404, null));
        $this->assertSame('client', $guessErrorType->invoke($client, 422, null));
        $this->assertSame('server', $guessErrorType->invoke($client, 500, null));
        $this->assertSame('invalid_json', $guessErrorType->invoke($client, 500, 'Syntax error'));
        $this->assertNull($guessErrorType->invoke($client, 302, null));

        $payload = new \MasyaSmv\FinamSdk\Dto\Transport\ApiPayload(['message' => 'Detailed error']);
        $this->assertSame('Response is not valid JSON: Syntax error', $resolveErrorMessage->invoke($client, 500, 'Syntax error', null));
        $this->assertSame('Detailed error', $resolveErrorMessage->invoke($client, 400, null, $payload));
        $this->assertSame('HTTP error: 500', $resolveErrorMessage->invoke($client, 500, null, null));

        /** @var \MasyaSmv\FinamSdk\Dto\Transport\ApiRequestContext $context */
        $context = $requestContext->invoke($client, 'POST', 'orders', ['query' => ['foo' => 'bar'], 'json' => ['baz' => 'qux']], 3);
        $this->assertSame('POST', $context->method());
        $this->assertSame('orders', $context->uri());
        $this->assertSame(['foo' => 'bar'], $context->query()->toArray());
        $this->assertSame(['baz' => 'qux'], $context->payload()->toArray());
        $this->assertSame(3, $context->attempt());

        /** @var \MasyaSmv\FinamSdk\Dto\Transport\ApiMeta $metaDto */
        $metaDto = $meta->invoke($client, ['X-Test' => ['v1', 2], 1 => ['skip-me']], 'GET', 'orders', ['query' => ['foo' => 'bar']], 1);
        $this->assertSame(['X-Test' => ['v1']], $metaDto->headers()->toArray());
        $this->assertSame('GET', $metaDto->request()?->method());

        /** @var array{headers: array<string, string>} $headers */
        $headers = $withAuthHeader->invoke($client, ['headers' => ['X-Test' => '1']]);
        $this->assertSame('Bearer token', $headers['headers']['Authorization']);

        $providerProperty = new ReflectionProperty(FinamClient::class, 'tokenProvider');
        $providerProperty->setAccessible(true);
        $provider = $this->createStub(TokenProviderInterface::class);
        $provider->method('getToken')->willReturn('');
        $providerProperty->setValue($client, $provider);

        /** @var array{headers: array<string, string>} $withoutAuth */
        $withoutAuth = $withAuthHeader->invoke($client, ['headers' => ['Authorization' => 'old']]);
        $this->assertArrayNotHasKey('Authorization', $withoutAuth['headers']);
    }

    public function testStaticTokenProviderFactoryAndManagerCoverage(): void
    {
        $provider = new StaticTokenProvider('token');
        $config = new FinamConfig('https://example.test', new FinamHttpConfig(3.0, 1.0, 2, 5, 'ua'));
        $factory = new FinamClientFactory($config);
        $client = $factory->withTokenProvider($provider);
        $auth = $this->createMock(AuthServiceInterface::class);
        $auth->expects($this->once())
            ->method('issueToken')
            ->with('secret')
            ->willReturn(new IssuedTokenDto('secret-jwt'));
        $manager = new FinamManager($factory, $auth);

        $this->assertSame('token', $provider->getToken());
        $this->assertSame('token', $client->getAccessToken());
        $this->assertSame('secret-jwt', $manager->issueToken('secret')->token());
        $this->assertInstanceOf(FinamClient::class, $manager->client('runtime-token'));
    }

    public function testStaticTokenProviderRejectsEmptyToken(): void
    {
        $this->expectException(\MasyaSmv\FinamSdk\Exceptions\TokenNotConfiguredException::class);
        (new StaticTokenProvider(''))->getToken();
    }
}
