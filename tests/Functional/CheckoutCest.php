<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\Order;
use app\models\Product;
use app\tests\Support\Fixtures\UserFixture;
use app\tests\Support\FunctionalTester;

final class CheckoutCest
{
    public function _fixtures(): array
    {
        return ['users' => UserFixture::class];
    }

    private function makeProduct(string $sku, int $stock = 5): Product
    {
        $product = new Product([
            'name' => 'Checkout Product ' . $sku,
            'slug' => 'checkout-product-' . strtolower($sku),
            'sku' => $sku,
            'price' => '15.00',
            'stock' => $stock,
            'status' => Product::STATUS_ACTIVE,
        ]);
        $product->save();

        return $product;
    }

    public function loggedInUserCanCompleteCheckout(FunctionalTester $I)
    {
        $product = $this->makeProduct('CHK-1', 5);

        $I->amLoggedInAs(100);
        $I->amOnRoute('catalog/view', ['id' => $product->id]);
        $I->submitForm('form[action*="cart%2Fadd"]', ['quantity' => '2']);
        $I->see($product->name);

        $I->click('Proceed to checkout');
        $I->see('Checkout', 'h1');
        $I->see('30.00'); // 2 * 15.00

        $I->submitForm('form[action*="cart%2Fplace-order"]', []);
        $I->see('Order #');
        $I->see($product->name);

        $product->refresh();
        $I->assertEquals(3, $product->stock);
        $I->assertEquals(1, Order::find()->where(['user_id' => 100])->count());
    }

    public function guestCartMergesIntoUserCartOnLogin(FunctionalTester $I)
    {
        $product = $this->makeProduct('CHK-2', 5);

        $I->amOnRoute('catalog/view', ['id' => $product->id]);
        $I->submitForm('form[action*="cart%2Fadd"]', ['quantity' => '1']);
        $I->see($product->name);

        $I->click('Log in');
        $I->submitForm('#login-form', [
            'LoginForm[username]' => 'demo',
            'LoginForm[password]' => 'demo',
        ]);

        $I->amOnPage(\yii\helpers\Url::to(['/cart/index']));
        $I->see($product->name);
    }
}
