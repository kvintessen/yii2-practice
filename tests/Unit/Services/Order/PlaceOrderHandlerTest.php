<?php

declare(strict_types=1);

namespace app\tests\Unit\Services\Order;

use app\models\Cart;
use app\models\Order;
use app\models\Product;
use app\services\Order\InsufficientStockException;
use app\services\Order\PlaceOrderCommand;
use app\services\Order\PlaceOrderHandler;
use app\tests\Support\Fixtures\UserFixture;
use DomainException;

final class PlaceOrderHandlerTest extends \Codeception\Test\Unit
{
    private PlaceOrderHandler $_handler;

    public function _fixtures(): array
    {
        return ['users' => UserFixture::class];
    }

    protected function _before()
    {
        $this->_handler = new PlaceOrderHandler();
    }

    private function makeProduct(string $sku, string $price, int $stock): Product
    {
        $product = new Product([
            'name' => 'Product ' . $sku,
            'slug' => 'product-' . strtolower($sku),
            'sku' => $sku,
            'price' => $price,
            'stock' => $stock,
        ]);
        $product->save();

        return $product;
    }

    public function testPlacingAnOrderReservesStockAndComputesTotal()
    {
        $product = $this->makeProduct('SKU-1', '10.00', 5);
        $cart = Cart::findOrCreateForUser(100);
        $cart->addItem($product, 3);

        $order = $this->_handler->handle(new PlaceOrderCommand(100, $cart->id));

        verify($order->status)->equals(Order::STATUS_NEW);
        verify((float) $order->total)->equals(30.0);
        verify(count($order->items))->equals(1);
        verify($order->items[0]->product_name)->equals($product->name);
        verify($order->items[0]->quantity)->equals(3);

        $product->refresh();
        verify($product->stock)->equals(2);

        $cart->refresh();
        verify(count($cart->items))->equals(0);
    }

    public function testInsufficientStockRollsBackTheWholeOrder()
    {
        $inStock = $this->makeProduct('SKU-2', '10.00', 1);
        $short = $this->makeProduct('SKU-3', '5.00', 1);
        $cart = Cart::findOrCreateForUser(100);
        $cart->addItem($inStock, 1);
        $cart->addItem($short, 2); // more than available

        $this->expectException(InsufficientStockException::class);

        try {
            $this->_handler->handle(new PlaceOrderCommand(100, $cart->id));
        } finally {
            $inStock->refresh();
            verify($inStock->stock)->equals(1); // untouched, transaction rolled back
            verify(Order::find()->count())->equals(0);
        }
    }

    public function testEmptyCartCannotBeOrdered()
    {
        $cart = Cart::findOrCreateForUser(100);

        $this->expectException(DomainException::class);
        $this->_handler->handle(new PlaceOrderCommand(100, $cart->id));
    }

    public function testCannotPlaceOrderForAnotherUsersCart()
    {
        $product = $this->makeProduct('SKU-4', '10.00', 5);
        $cart = Cart::findOrCreateForUser(100);
        $cart->addItem($product, 1);

        $this->expectException(DomainException::class);
        $this->_handler->handle(new PlaceOrderCommand(101, $cart->id));
    }
}
