<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Instrument;

use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class ScheduleRequest
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

    /**
     * @return array<string, string>
     */
    public function toQuery(): array
    {
        return [
            'symbol' => $this->symbol,
            'account_id' => $this->accountId,
        ];
    }
}
