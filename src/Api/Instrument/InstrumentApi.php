<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Api\Instrument;

use MasyaSmv\FinamSdk\Client\FinamClient;

/**
 * InstrumentApi — инструменты/справочники/поиск тикеров.
 */
final class InstrumentApi
{
    public function __construct(private FinamClient $client)
    {
    }

    // public function search(array $query = []): array { return $this->client->get('/instruments', $query); }
}
