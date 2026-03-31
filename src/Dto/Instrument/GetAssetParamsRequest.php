<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Instrument;

use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class GetAssetParamsRequest
{
    public function __construct(
        private string $symbol,
        private string $accountId,
    ) {
        if ($this->symbol === '') {
            throw new InvalidRequestException('Symbol must not be empty.');
        }

        if ($this->accountId === '') {
            throw new InvalidRequestException('AccountId must not be empty.');
        }
    }

    public function symbol(): string
    {
        return $this->symbol;
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
