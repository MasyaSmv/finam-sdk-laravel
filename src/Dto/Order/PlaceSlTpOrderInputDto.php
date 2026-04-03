<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Order;

use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class PlaceSlTpOrderInputDto
{
    public function __construct(
        private string $symbol,
        private string $side,
        private ?string $quantitySl = null,
        private ?string $slPrice = null,
        private ?string $slLimitPrice = null,
        private ?string $quantityTp = null,
        private ?string $tpPrice = null,
        private ?string $tpGuardSpread = null,
        private ?string $comment = null,
    ) {
        if ($this->symbol === '') {
            throw new InvalidRequestException('Symbol must not be empty.');
        }

        if ($this->side === '') {
            throw new InvalidRequestException('Side must not be empty.');
        }

        if (
            $this->quantitySl === null
            && $this->quantityTp === null
        ) {
            throw new InvalidRequestException('At least one SL or TP branch must be configured.');
        }

        if ($this->quantitySl !== null && $this->slPrice === null) {
            throw new InvalidRequestException('SL price is required when SL quantity is configured.');
        }

        if ($this->quantityTp !== null && $this->tpPrice === null) {
            throw new InvalidRequestException('TP price is required when TP quantity is configured.');
        }
    }

    /**
     * @return array<string, string|array{value: string}>
     */
    public function toPayload(): array
    {
        $payload = [
            'symbol' => $this->symbol,
            'side' => $this->side,
        ];

        if ($this->quantitySl !== null) {
            $payload['quantity_sl'] = ['value' => $this->quantitySl];
            $payload['sl_price'] = ['value' => (string) $this->slPrice];
        }

        if ($this->slLimitPrice !== null) {
            $payload['limit_price'] = ['value' => $this->slLimitPrice];
        }

        if ($this->quantityTp !== null) {
            $payload['quantity_tp'] = ['value' => $this->quantityTp];
            $payload['tp_price'] = ['value' => (string) $this->tpPrice];
        }

        if ($this->tpGuardSpread !== null) {
            $payload['tp_guard_spread'] = ['value' => $this->tpGuardSpread];
        }

        if ($this->comment !== null) {
            $payload['comment'] = $this->comment;
        }

        return $payload;
    }
}
