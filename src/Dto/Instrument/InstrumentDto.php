<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Instrument;

final class InstrumentDto
{
    public function __construct(
        private string $symbol,
        private ?string $id = null,
        private ?string $ticker = null,
        private ?string $mic = null,
        private ?string $type = null,
        private ?string $name = null,
        private ?string $board = null,
        private ?int $decimals = null,
        private ?string $minStep = null,
        private ?string $quoteCurrency = null,
        private ?string $expirationDate = null,
        private ?string $lotSize = null,
        private ?string $isin = null,
    ) {
    }

    public function symbol(): string
    {
        return $this->symbol;
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function ticker(): ?string
    {
        return $this->ticker;
    }

    public function mic(): ?string
    {
        return $this->mic;
    }

    public function type(): ?string
    {
        return $this->type;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function board(): ?string
    {
        return $this->board;
    }

    public function decimals(): ?int
    {
        return $this->decimals;
    }

    public function minStep(): ?string
    {
        return $this->minStep;
    }

    public function quoteCurrency(): ?string
    {
        return $this->quoteCurrency;
    }

    public function expirationDate(): ?string
    {
        return $this->expirationDate;
    }

    public function shortName(): string
    {
        return $this->name ?? $this->symbol;
    }

    public function description(): ?string
    {
        return $this->name;
    }

    public function market(): ?string
    {
        return $this->mic;
    }

    public function currency(): ?string
    {
        return $this->quoteCurrency;
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
