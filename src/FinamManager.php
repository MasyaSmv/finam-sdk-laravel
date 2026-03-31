<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk;

use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Client\FinamClientFactory;
use MasyaSmv\FinamSdk\Contracts\FinamManagerInterface;
use MasyaSmv\FinamSdk\Contracts\FinamSessionInterface;
use MasyaSmv\FinamSdk\Session\FinamSession;

final class FinamManager implements FinamManagerInterface
{
    public function __construct(private FinamClientFactory $factory)
    {
    }

    public function connect(string $token): FinamSessionInterface
    {
        $client = $this->factory->withToken($token);

        return new FinamSession(
            connectApi: $client->connect(),
            accountApi: $client->account(),
            orderApi: $client->order(),
        );
    }

    public function client(?string $token = null): FinamClient
    {
        if ($token === null) {
            return $this->factory->default();
        }

        return $this->factory->withToken($token);
    }
}
