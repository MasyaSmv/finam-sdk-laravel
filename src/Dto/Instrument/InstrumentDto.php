<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Instrument;

final class InstrumentDto
{
    public function __construct(
        private string $symbol,
        private string $shortName,
        private ?string $description = null,
        private ?string $market = null,
        private ?string $currency = null,
        private ?string $lotSize = null,
        private ?string $isin = null,
    ) {
    }

    public function symbol(): string
    {
        return $this->symbol;
    }

    public function shortName(): string
    {
        return $this->shortName;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function market(): ?string
    {
        return $this->market;
    }

    public function currency(): ?string
    {
        return $this->currency;
    }

    public function lotSize(): ?string
    {
        return $this->lotSize;
    }

    public function isin(): ?string
    {
        return $this->isin;
    }
}
