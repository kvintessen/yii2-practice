<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\Cart;
use app\models\Product;
use app\tests\Support\Fixtures\UserFixture;

final class CartTest extends \Codeception\Test\Unit
{
    public function _fixtures(): array
    {
        return ['users' => UserFixture::class];
    }

    private function makeProduct(string $sku, string $price, int $stock = 10): Product
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

    public function testFindOrCreateForUserIsIdempotent()
    {
        $cart = Cart::findOrCreateForUser(100);
        $again = Cart::findOrCreateForUser(100);

        verify($again->id)->equals($cart->id);
    }

    public function testFindOrCreateForSessionIsIdempotent()
    {
        $cart = Cart::findOrCreateForSession('sess-abc');
        $again = Cart::findOrCreateForSession('sess-abc');

        verify($again->id)->equals($cart->id);
    }

    public function testAddItemIncreasesQuantityForExistingProduct()
    {
        $cart = Cart::findOrCreateForUser(100);
        $product = $this->makeProduct('SKU-1', '10.00');

        $cart->addItem($product, 2);
        $cart->addItem($product, 3);

        $item = $cart->items[0];
        verify(count($cart->items))->equals(1);
        verify($item->quantity)->equals(5);
    }

    public function testGetTotalSumsLineItems()
    {
        $cart = Cart::findOrCreateForUser(100);
        $productA = $this->makeProduct('SKU-A', '10.00');
        $productB = $this->makeProduct('SKU-B', '2.50');

        $cart->addItem($productA, 2);
        $cart->addItem($productB, 4);

        verify($cart->getTotal())->equals(30.0);
    }

    public function testMergeIntoCombinesQuantitiesAndDeletesSource()
    {
        $guestCart = Cart::findOrCreateForSession('sess-merge');
        $userCart = Cart::findOrCreateForUser(101);
        $product = $this->makeProduct('SKU-M', '5.00');

        $guestCart->addItem($product, 2);
        $userCart->addItem($product, 1);

        $guestCart->mergeInto($userCart);

        $userCart->refresh();
        verify(count($userCart->items))->equals(1);
        verify($userCart->items[0]->quantity)->equals(3);
        verify(Cart::findOne($guestCart->id))->null();
    }
}
