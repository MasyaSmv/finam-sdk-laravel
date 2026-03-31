<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests\Support;

use MasyaSmv\FinamSdk\Contracts\Api\AccountApiInterface;
use MasyaSmv\FinamSdk\Dto\Account\GetAccountRequest;
use MasyaSmv\FinamSdk\Dto\Account\TradesRequest;
use MasyaSmv\FinamSdk\Dto\Account\TransactionsRequest;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

final class AccountApiStub implements AccountApiInterface
{
    public function __construct(private ApiResponse $response)
    {
    }

    public function account(GetAccountRequest $request): ApiResponse
    {
        return $this->response;
    }

    public function trades(TradesRequest $request): ApiResponse
    {
        return $this->response;
    }

    public function transactions(TransactionsRequest $request): ApiResponse
    {
        return $this->response;
    }
}
