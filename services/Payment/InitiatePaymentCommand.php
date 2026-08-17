<?php

declare(strict_types=1);

namespace app\services\Payment;

final readonly class InitiatePaymentCommand
{
    public function __construct(
        public int $userId,
        public int $orderId,
        public string $providerCode,
    ) {
    }
}
