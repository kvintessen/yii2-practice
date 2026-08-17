<?php

declare(strict_types=1);

namespace app\services\Payment;

use RuntimeException;

/**
 * A call to the provider's API itself failed (network error, non-2xx
 * response) — distinct from InvalidWebhookException, which is about not
 * trusting an inbound callback.
 */
final class PaymentGatewayException extends RuntimeException implements PaymentExceptionInterface
{
}
