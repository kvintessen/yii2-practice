<?php

declare(strict_types=1);

namespace app\services\Payment;

/**
 * Gateway-facing payment outcome, decoupled from the app's Payment AR model
 * so a gateway adapter never needs to know about ActiveRecord.
 */
enum PaymentStatus: string
{
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Canceled = 'canceled';
    case Failed = 'failed';
}
