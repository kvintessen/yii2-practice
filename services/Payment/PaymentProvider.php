<?php

declare(strict_types=1);

namespace app\services\Payment;

/**
 * Single source of truth for a gateway's machine identity — otherwise
 * every consumer (Payment::$provider values, route params, tests) carries
 * its own copy of the same string literal with nothing catching a typo.
 */
enum PaymentProvider: string
{
    case YooKassa = 'yookassa';
    case Robokassa = 'robokassa';
    case Fake = 'fake';
}
