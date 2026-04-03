<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Account;

use DateTimeImmutable;
use MasyaSmv\FinamSdk\Dto\Shared\MoneyDto;

final class OperationDto
{
    public function __construct(
        private string $id,
        private string $accountId,
        private string $category,
        private string $transactionCategory,
        private string $transactionName,
        private string $symbol,
        private DateTimeImmutable $occurredAt,
        private MoneyDto $change,
        private ?string $changeQuantity = null,
        private ?OperationTradeDto $trade = null,
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function accountId(): string
    {
        return $this->accountId;
    }

    public function category(): string
    {
        return $this->category;
    }

    public function transactionCategory(): string
    {
        return $this->transactionCategory;
    }

    public function transactionName(): string
    {
        return $this->transactionName;
    }

    public function symbol(): string
    {
        return $this->symbol;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function change(): MoneyDto
    {
        return $this->change;
    }

    public function changeQuantity(): ?string
    {
        return $this->changeQuantity;
    }

    public function trade(): ?OperationTradeDto
    {
        return $this->trade;
    }
}
