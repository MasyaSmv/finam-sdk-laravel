<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Instrument;

final class AssetsRequest
{
    /**
     * @param array<string, mixed> $query
     */
    public function __construct(private array $query = [])
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toQuery(): array
    {
        return $this->query;
    }
}
