<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Api\Account;

use MasyaSmv\FinamSdk\Contracts\Api\AccountApiInterface;
use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Dto\Account\GetAccountRequest;
use MasyaSmv\FinamSdk\Dto\Account\TradesRequest;
use MasyaSmv\FinamSdk\Dto\Account\TransactionsRequest;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

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
     */
    public function account(GetAccountRequest $request): ApiResponse
    {
        $accountId = $request->accountId();

        return $this->client->get("/accounts/{$accountId}");
    }

    /**
     * История сделок по счёту.
     */
    public function trades(TradesRequest $request): ApiResponse
    {
        $accountId = $request->accountId();

        return $this->client->get("/accounts/{$accountId}/trades", $request->toQuery());
    }

    /**
     * История транзакций по счёту.
     */
    public function transactions(TransactionsRequest $request): ApiResponse
    {
        $accountId = $request->accountId();

        return $this->client->get("/accounts/{$accountId}/transactions", $request->toQuery());
    }
}
