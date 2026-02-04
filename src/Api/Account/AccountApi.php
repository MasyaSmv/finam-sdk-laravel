<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Api\Account;

use MasyaSmv\FinamSdk\Contracts\Api\AccountApiInterface;
use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Dto\Account\GetAccountRequest;
use MasyaSmv\FinamSdk\Dto\Account\TradesRequest;
use MasyaSmv\FinamSdk\Dto\Account\TransactionsRequest;

/**
 * AccountApi — счета, сделки, транзакции, позиции.
 *
 * Сейчас это "каркас", чтобы FinamClient не разрастался.
 * Дальше просто добавляешь методы-эндпоинты сюда.
 */
final class AccountApi implements AccountApiInterface
{
    public function __construct(private FinamClient $client)
    {
    }

    /**
     * Детали счёта.
     *
     * @param GetAccountRequest $request
     *
     * @return array<string, mixed>
     */
    public function account(GetAccountRequest $request): array
    {
        $accountId = $request->accountId();

        return $this->client->get("/accounts/{$accountId}");
    }

    /**
     * История сделок по счёту.
     *
     * @param TradesRequest $request
     *
     * @return array<string, mixed>
     */
    public function trades(TradesRequest $request): array
    {
        $accountId = $request->accountId();

        return $this->client->get("/accounts/{$accountId}/trades", $request->toQuery());
    }

    /**
     * История транзакций по счёту.
     *
     * @param TransactionsRequest $request
     *
     * @return array<string, mixed>
     */
    public function transactions(TransactionsRequest $request): array
    {
        $accountId = $request->accountId();

        return $this->client->get("/accounts/{$accountId}/transactions", $request->toQuery());
    }
}
