<?php

declare(strict_types=1);

namespace app\services\Payment;

use DomainException;

final class InvalidWebhookException extends DomainException
{
}
