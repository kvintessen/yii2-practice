<?php

declare(strict_types=1);

namespace app\services\Order;

use app\models\Cart;
use app\models\Order;
use app\models\OrderItem;
use DomainException;
use Throwable;
use Yii;

/**
 * Turns a cart into an order: snapshots each line item, atomically reserves
 * stock, and computes the order total — all inside one transaction so a
 * failure at any step (missing stock, a save error) leaves nothing changed.
 */
final class PlaceOrderHandler
{
    public function handle(PlaceOrderCommand $command): Order
    {
        $cart = Cart::findOne($command->cartId);

        if ($cart === null || $cart->user_id !== $command->userId) {
            throw new DomainException('Cart not found for this user.');
        }

        $items = $cart->items;

        if ($items === []) {
            throw new DomainException('Cannot place an order from an empty cart.');
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $order = new Order([
                'user_id' => $command->userId,
                'status' => Order::STATUS_NEW,
                'total' => 0,
            ]);

            if (!$order->save()) {
                throw new DomainException('Unable to create the order.');
            }

            $total = 0.0;

            foreach ($items as $item) {
                // cart_item.product_id cascades on product delete, so the
                // relation can't be null while this row still exists.
                $product = $item->product;

                $reserved = $db->createCommand(
                    'UPDATE {{%product}} SET stock = stock - :qty WHERE id = :id AND stock >= :qty',
                    [':qty' => $item->quantity, ':id' => $product->id],
                )->execute();

                if ($reserved !== 1) {
                    throw new InsufficientStockException($product, $item->quantity);
                }

                $lineTotal = round((float) $product->price * $item->quantity, 2);

                $orderItem = new OrderItem([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $item->quantity,
                    'line_total' => $lineTotal,
                ]);

                if (!$orderItem->save()) {
                    throw new DomainException('Unable to save an order item.');
                }

                $total += $lineTotal;
            }

            $order->total = (string) round($total, 2);

            if (!$order->save()) {
                throw new DomainException('Unable to update the order total.');
            }

            $cart->unlinkAll('items', true);

            $transaction->commit();

            return $order;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }
    }
}
