<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Instrument;

use MasyaSmv\FinamSdk\Collections\InstrumentCollection;

final class AllAssetsPageDto
{
    public function __construct(
        private InstrumentCollection $assets,
        private ?int $nextCursor,
    ) {
    }

    public function assets(): InstrumentCollection
    {
        return $this->assets;
    }

    public function nextCursor(): ?int
    {
        return $this->nextCursor;
    }

    public function hasNextPage(): bool
    {
        return $this->nextCursor !== null;
    }
}
