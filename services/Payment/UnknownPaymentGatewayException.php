<?php

declare(strict_types=1);

namespace app\services\Payment;

use OutOfBoundsException;

final class UnknownPaymentGatewayException extends OutOfBoundsException implements PaymentExceptionInterface
{
}
