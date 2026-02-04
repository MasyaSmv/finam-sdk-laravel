<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Instrument;

use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class OptionsChainRequest
{
    public function __construct(
        private string $underlyingSymbol,
        private ?string $root = null,
        private ?string $expirationDate = null,
    ) {
        if ($this->underlyingSymbol === '') {
            throw new InvalidRequestException('Underlying symbol must not be empty.');
        }
    }

    /**
     * @return array<string, string>
     */
    public function toQuery(): array
    {
        $query = [
            'underlying_symbol' => $this->underlyingSymbol,
        ];

        if ($this->root !== null && $this->root !== '') {
            $query['root'] = $this->root;
        }

        if ($this->expirationDate !== null && $this->expirationDate !== '') {
            $query['expiration_date'] = $this->expirationDate;
        }

        return $query;
    }
}
