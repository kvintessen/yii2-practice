<?php

declare(strict_types=1);

namespace app\services\Order;

use app\models\Product;
use RuntimeException;

final class InsufficientStockException extends RuntimeException
{
    public function __construct(public readonly Product $product, public readonly int $requestedQuantity)
    {
        parent::__construct(sprintf(
            'Not enough stock for "%s": requested %d, only %d available.',
            $product->name,
            $requestedQuantity,
            $product->stock,
        ));
    }
}
