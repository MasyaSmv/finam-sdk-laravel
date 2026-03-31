<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Order;

use DateTimeImmutable;

final class OrderDto
{
    public function __construct(
        private string $orderId,
        private ?string $execId,
        private string $status,
        private string $accountId,
        private string $symbol,
        private string $quantity,
        private string $side,
        private string $type,
        private string $timeInForce,
        private ?string $clientOrderId = null,
        private ?string $comment = null,
        private ?string $limitPrice = null,
        private ?string $stopPrice = null,
        private ?DateTimeImmutable $transactAt = null,
        private ?DateTimeImmutable $acceptAt = null,
        private ?DateTimeImmutable $withdrawAt = null,
        private ?string $initialQuantity = null,
        private ?string $executedQuantity = null,
        private ?string $remainingQuantity = null,
    ) {
    }

    public function orderId(): string
    {
        return $this->orderId;
    }

    public function execId(): ?string
    {
        return $this->execId;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function accountId(): string
    {
        return $this->accountId;
    }

    public function symbol(): string
    {
        return $this->symbol;
    }

    public function quantity(): string
    {
        return $this->quantity;
    }

    public function side(): string
    {
        return $this->side;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function timeInForce(): string
    {
        return $this->timeInForce;
    }

    public function clientOrderId(): ?string
    {
        return $this->clientOrderId;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function limitPrice(): ?string
    {
        return $this->limitPrice;
    }

    public function stopPrice(): ?string
    {
        return $this->stopPrice;
    }

    public function transactAt(): ?DateTimeImmutable
    {
        return $this->transactAt;
    }

    public function acceptAt(): ?DateTimeImmutable
    {
        return $this->acceptAt;
    }

    public function withdrawAt(): ?DateTimeImmutable
    {
        return $this->withdrawAt;
    }

    public function initialQuantity(): ?string
    {
        return $this->initialQuantity;
    }

    public function executedQuantity(): ?string
    {
        return $this->executedQuantity;
    }

    public function remainingQuantity(): ?string
    {
        return $this->remainingQuantity;
    }
}
