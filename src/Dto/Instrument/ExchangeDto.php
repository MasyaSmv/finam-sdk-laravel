<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Instrument;

final class ExchangeDto
{
    public function __construct(
        private string $mic,
        private string $name,
    ) {
    }

    public function mic(): string
    {
        return $this->mic;
    }

    public function name(): string
    {
        return $this->name;
    }
}
