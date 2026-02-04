<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Api\Market;

use MasyaSmv\FinamSdk\Client\FinamClient;

/**
 * MarketApi — котировки/свечи/стакан/лента (market data).
 */
final class MarketApi
{
    public function __construct(private FinamClient $client)
    {
    }

    // public function candles(array $query): array { return $this->client->get('/market/candles', $query); }
}
