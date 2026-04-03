<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests\Support;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use MasyaSmv\FinamSdk\Auth\TokenProviderInterface;
use MasyaSmv\FinamSdk\Client\FinamClient;
use ReflectionProperty;

trait HttpClientTestHelper
{
    /**
     * @param list<Response|\Throwable> $queue
     * @param array<int, array<string, mixed>> $history
     */
    private function makeClientWithQueue(
        array $queue,
        array &$history = [],
        string|TokenProviderInterface $token = 'runtime-token',
    ): FinamClient {
        $mock = new MockHandler($queue);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));

        $client = new FinamClient($token, 'https://example.test');
        $http = new GuzzleClient([
            'base_uri' => 'https://example.test/',
            'handler' => $stack,
            'http_errors' => false,
        ]);

        $property = new ReflectionProperty(FinamClient::class, 'http');
        $property->setAccessible(true);
        $property->setValue($client, $http);

        return $client;
    }
}
