<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Api\Connect;

use MasyaSmv\FinamSdk\Contracts\Api\ConnectApiInterface;
use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Dto\Connect\TokenDetailsRequest;

/**
 * ConnectApi — методы, связанные с подключением/сессиями/проверкой токена.
 *
 * Здесь логично держать:
 * - sessions/details (tokenDetails)
 * - в будущем: refresh/revoke/healthcheck и т.п. (если у Finam есть)
 */
final class ConnectApi implements ConnectApiInterface
{
    public function __construct(private FinamClient $client)
    {
    }

    /**
     * TokenDetails (Auth Service).
     * По документации: POST /sessions/details и токен передаётся в теле запроса.
     *
     * @return array<string, mixed>
     */
    public function tokenDetails(): array
    {
        return $this->client->post('/sessions/details', [
            'token' => $this->client->getAccessToken(),
        ]);
    }
}
