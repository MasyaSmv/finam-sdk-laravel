<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Api;

use MasyaSmv\FinamSdk\Dto\Account\GetAccountRequest;
use MasyaSmv\FinamSdk\Dto\Account\TradesRequest;
use MasyaSmv\FinamSdk\Dto\Account\TransactionsRequest;

interface AccountApiInterface
{
    /**
     * @return array<string, mixed>
     */
    public function account(GetAccountRequest $request): array;

    /**
     * @return array<string, mixed>
     */
    public function trades(TradesRequest $request): array;

    /**
     * @return array<string, mixed>
     */
    public function transactions(TransactionsRequest $request): array;
}
