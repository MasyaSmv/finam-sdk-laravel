<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk;

use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Client\FinamClientFactory;
use MasyaSmv\FinamSdk\Contracts\AuthServiceInterface;
use MasyaSmv\FinamSdk\Contracts\FinamManagerInterface;
use MasyaSmv\FinamSdk\Contracts\FinamSessionInterface;
use MasyaSmv\FinamSdk\Dto\Auth\IssuedTokenDto;
use MasyaSmv\FinamSdk\Session\FinamSession;

final class FinamManager implements FinamManagerInterface
{
    public function __construct(
        private FinamClientFactory $factory,
        private AuthServiceInterface $authService,
    ) {
    }

    public function issueToken(string $secret): IssuedTokenDto
    {
        return $this->authService->issueToken($secret);
    }

    public function connect(string $token): FinamSessionInterface
    {
        $client = $this->factory->withToken($token);

        return FinamSession::fromApis(
            connectApi: $client->connect(),
            accountApi: $client->account(),
            orderApi: $client->order(),
            instrumentApi: $client->instrument(),
            marketApi: $client->market(),
            usageMetricsApi: $client->usageMetrics(),
            reportsApi: $client->reports(),
        );
    }

    public function client(string $token): FinamClient
    {
        return $this->factory->withToken($token);
    }
}
