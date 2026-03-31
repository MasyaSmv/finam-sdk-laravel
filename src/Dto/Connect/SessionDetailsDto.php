<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Connect;

use DateTimeImmutable;
use MasyaSmv\FinamSdk\Collections\StringCollection;

final class SessionDetailsDto
{
    public function __construct(
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $expiresAt,
        private StringCollection $mdPermissions,
        private StringCollection $accountIds,
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

    public function mdPermissions(): StringCollection
    {
        return $this->mdPermissions;
    }

    public function accountIds(): StringCollection
    {
        return $this->accountIds;
    }

    public function readonly(): bool
    {
        return $this->readonly;
    }
}
