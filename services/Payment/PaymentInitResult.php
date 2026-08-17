<?php

declare(strict_types=1);

namespace app\services\Payment;

final class PaymentInitResult
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $confirmationUrl,
        public readonly string $rawPayload,
    ) {
    }
}
