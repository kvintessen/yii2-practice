<?php

declare(strict_types=1);

namespace app\services\Order;

final class PlaceOrderCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly int $cartId,
    ) {
    }
}
