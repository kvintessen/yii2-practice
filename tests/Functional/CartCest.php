<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\Product;
use app\tests\Support\FunctionalTester;

final class CartCest
{
    private function makeProduct(string $sku, int $stock = 5): Product
    {
        $product = new Product([
            'name' => 'Cart Product ' . $sku,
            'slug' => 'cart-product-' . strtolower($sku),
            'sku' => $sku,
            'price' => '10.00',
            'stock' => $stock,
            'status' => Product::STATUS_ACTIVE,
        ]);
        $product->save();

        return $product;
    }

    public function guestCanAddUpdateAndRemoveAnItem(FunctionalTester $I)
    {
        $product = $this->makeProduct('CART-1');

        $I->amOnRoute('catalog/view', ['id' => $product->id]);
        $I->submitForm('form[action*="cart%2Fadd"]', ['quantity' => '1']);
        $I->see('Your Cart', 'h1');
        $I->see($product->name);
        $I->see('1'); // quantity

        $I->submitForm('form[action*="cart%2Fupdate-quantity"]', ['quantity' => '3']);
        $I->see('30'); // 3 * 10.00 line total

        $I->submitForm('form[action*="cart%2Fremove"]', []);
        $I->see('Your cart is empty');
    }

    public function guestCheckoutRedirectsToLogin(FunctionalTester $I)
    {
        $I->amOnRoute('cart/checkout');
        $I->seeInCurrentUrl('site%2Flogin');
    }
}
