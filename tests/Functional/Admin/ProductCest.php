<?php

declare(strict_types=1);

namespace app\tests\Functional\Admin;

use app\models\Category;
use app\models\Product;
use app\tests\Support\Fixtures\AdminRoleAssignmentFixture;
use app\tests\Support\Fixtures\UserFixture;
use app\tests\Support\FunctionalTester;
use yii\helpers\Url;

final class ProductCest
{
    public function _fixtures(): array
    {
        return [
            'users' => UserFixture::class,
            'adminRole' => AdminRoleAssignmentFixture::class,
        ];
    }

    public function _before(FunctionalTester $I)
    {
        $I->amLoggedInAsAdmin(100);
    }

    public function createAssignsCategoriesAndCanBeDeleted(FunctionalTester $I)
    {
        $category = new Category(['name' => 'Phones', 'slug' => 'phones']);
        $I->assertTrue($category->save());

        $I->amOnRoute('admin/product/create');
        $I->submitForm('.product-form form', [
            'Product[name]' => 'Phone X',
            'Product[slug]' => 'phone-x',
            'Product[sku]' => 'PHONE-X',
            'Product[price]' => '199.99',
            'Product[status]' => Product::STATUS_ACTIVE,
            'Product[categoryIds][]' => (string) $category->id,
        ]);
        $I->see('Phone X', 'h1');

        $product = Product::findOne(['slug' => 'phone-x']);
        $I->assertNotNull($product);
        $I->assertCount(1, $product->categories);
        $I->assertEquals($category->id, $product->categories[0]->id);

        $I->sendAjaxRequest('POST', Url::to(['/admin/product/delete', 'id' => $product->id]));
        $I->seeResponseCodeIsRedirection();
        $I->assertNull(Product::findOne($product->id));
    }
}
