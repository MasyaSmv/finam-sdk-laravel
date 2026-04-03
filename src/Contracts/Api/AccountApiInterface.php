<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Api;

use MasyaSmv\FinamSdk\Dto\Account\GetAccountRequest;
use MasyaSmv\FinamSdk\Dto\Account\TradesRequest;
use MasyaSmv\FinamSdk\Dto\Account\TransactionsRequest;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

interface AccountApiInterface
{
    public function account(GetAccountRequest $request): ApiResponse;

    public function trades(TradesRequest $request): ApiResponse;

    public function transactions(TransactionsRequest $request): ApiResponse;
}
