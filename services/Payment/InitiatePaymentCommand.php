<?php

declare(strict_types=1);

namespace app\services\Payment;

final class InitiatePaymentCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly int $orderId,
        public readonly string $providerCode,
    ) {
    }
}
