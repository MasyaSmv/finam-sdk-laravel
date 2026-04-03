<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Collections;

use Illuminate\Support\Collection;
use MasyaSmv\FinamSdk\Dto\Order\OrderDto;

final class OrderCollection extends Collection
{
    /**
     * @param list<OrderDto> $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }

    public function findById(string $orderId): ?OrderDto
    {
        foreach ($this->items as $order) {
            if ($order->orderId() === $orderId) {
                return $order;
            }
        }

        return null;
    }
}
