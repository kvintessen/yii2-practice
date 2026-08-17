<?php

declare(strict_types=1);

namespace app\services\Payment;

final readonly class PaymentInitResult
{
    public function __construct(
        public string $externalId,
        public string $confirmationUrl,
        public string $rawPayload,
    ) {
    }
}
