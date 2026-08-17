<?php

declare(strict_types=1);

namespace app\services\Payment;

use Throwable;

/**
 * Marker for every exception this module's gateway layer can throw, so
 * callers (PaymentController) can catch one thing instead of enumerating
 * every concrete class — including ones added by future gateways. Extends
 * Throwable (rather than being a bare marker) so catch blocks can call
 * getMessage() etc. without a second, more specific catch type.
 */
interface PaymentExceptionInterface extends Throwable
{
}
