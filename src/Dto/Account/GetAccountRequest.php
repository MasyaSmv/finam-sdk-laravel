<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Account;

use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class GetAccountRequest
{
    public function __construct(private string $accountId)
    {
        if ($this->accountId === '') {
            throw new InvalidRequestException('AccountId must not be empty.');
        }
    }

    public function accountId(): string
    {
        return $this->accountId;
    }
}
