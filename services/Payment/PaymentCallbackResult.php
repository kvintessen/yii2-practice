<?php

declare(strict_types=1);

namespace app\services\Payment;

final readonly class PaymentCallbackResult
{
    public function __construct(
        public string $externalId,
        public PaymentStatus $status,
        public string $rawPayload,
    ) {
    }
}
