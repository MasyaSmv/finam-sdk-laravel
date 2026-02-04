<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Api\Account;

use MasyaSmv\FinamSdk\Client\FinamClient;

/**
 * AccountApi — счета, сделки, транзакции, позиции.
 *
 * Сейчас это "каркас", чтобы FinamClient не разрастался.
 * Дальше просто добавляешь методы-эндпоинты сюда.
 */
final class AccountApi
{
    public function __construct(private FinamClient $client)
    {
    }

    // Примеры будущих методов (реальные пути подставишь по документации):
    // public function accounts(): array { return $this->client->get('/accounts'); }
    // public function transactions(string $accountId, array $query = []): array { ... }
}
