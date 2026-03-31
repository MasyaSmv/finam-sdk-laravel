<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Instrument;

use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class ExchangesRequest
{
    public function __construct(private string $accountId)
    {
        if ($this->accountId === '') {
            throw new InvalidRequestException('Account ID must not be empty.');
        }
    }

    public function accountId(): string
    {
        return $this->accountId;
    }

    /**
     * @return array{account_id: string}
     */
    public function toQuery(): array
    {
        return [
            'account_id' => $this->accountId,
        ];
    }
}
