<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Instrument;

use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class GetAssetRequest
{
    public function __construct(
        private string $symbol,
        private ?string $accountId = null,
    ) {
        if ($this->symbol === '') {
            throw new InvalidRequestException('Symbol must not be empty.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toQuery(): array
    {
        $query = [
            'symbol' => $this->symbol,
        ];

        if ($this->accountId !== null && $this->accountId !== '') {
            $query['account_id'] = $this->accountId;
        }

        return $query;
    }
}
