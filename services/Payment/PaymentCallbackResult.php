<?php

declare(strict_types=1);

namespace app\services\Payment;

final class PaymentCallbackResult
{
    public function __construct(
        public readonly string $externalId,
        public readonly PaymentStatus $status,
        public readonly string $rawPayload,
    ) {
    }
}
