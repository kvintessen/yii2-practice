<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\models\Cart;
use app\models\Product;
use app\services\CartMergeService;
use app\tests\Support\Fixtures\UserFixture;
use Yii;

final class CartMergeServiceTest extends \Codeception\Test\Unit
{
    private CartMergeService $_service;

    public function _fixtures(): array
    {
        return ['users' => UserFixture::class];
    }

    protected function _before()
    {
        $this->_service = new CartMergeService();
    }

    private function makeProduct(string $sku): Product
    {
        $product = new Product([
            'name' => 'Product ' . $sku,
            'slug' => 'product-' . strtolower($sku),
            'sku' => $sku,
            'price' => '9.99',
            'stock' => 10,
        ]);
        $product->save();

        return $product;
    }

    public function testMergesGuestCartIntoUserCartAndClearsSessionCart()
    {
        $sessionId = Yii::$app->session->getId();
        $guestCart = Cart::findOrCreateForSession($sessionId);
        $product = $this->makeProduct('MERGE-1');
        $guestCart->addItem($product, 2);

        $this->_service->mergeGuestCartIntoUser($sessionId, 101);

        $userCart = Cart::findOne(['user_id' => 101]);
        verify($userCart)->notEmpty();
        verify(count($userCart->items))->equals(1);
        verify($userCart->items[0]->quantity)->equals(2);
        verify(Cart::findOne(['session_id' => $sessionId]))->null();
    }

    public function testNoGuestCartIsANoop()
    {
        $this->_service->mergeGuestCartIntoUser(Yii::$app->session->getId(), 101);

        verify(Cart::findOne(['user_id' => 101]))->null();
    }
}
