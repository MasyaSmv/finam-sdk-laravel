<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Instrument;

use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class AllAssetsRequest
{
    public function __construct(
        private ?int $cursor = null,
        private bool $onlyActive = false,
        private bool $onlyDisabled = false,
    ) {
        if ($this->cursor !== null && $this->cursor < 0) {
            throw new InvalidRequestException('Cursor must be null or a non-negative integer.');
        }

        if ($this->onlyActive && $this->onlyDisabled) {
            throw new InvalidRequestException('onlyActive and onlyDisabled cannot both be true.');
        }
    }

    public function cursor(): ?int
    {
        return $this->cursor;
    }

    public function onlyActive(): bool
    {
        return $this->onlyActive;
    }

    public function onlyDisabled(): bool
    {
        return $this->onlyDisabled;
    }

    /**
     * @return array<string, bool|int>
     */
    public function toQuery(): array
    {
        $query = [];

        if ($this->cursor !== null) {
            $query['cursor'] = $this->cursor;
        }

        if ($this->onlyActive) {
            $query['only_active'] = true;
        }

        if ($this->onlyDisabled) {
            $query['only_disabled'] = true;
        }

        return $query;
    }
}
