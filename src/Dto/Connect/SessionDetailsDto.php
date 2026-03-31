<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Connect;

use DateTimeImmutable;

final class SessionDetailsDto
{
    /**
     * @param list<string> $accountIds
     */
    public function __construct(
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $expiresAt,
        private array $accountIds,
        private bool $readonly,
    ) {
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /**
     * @return list<string>
     */
    public function accountIds(): array
    {
        return $this->accountIds;
    }

    public function readonly(): bool
    {
        return $this->readonly;
    }
}
