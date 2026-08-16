<?php

declare(strict_types=1);

namespace app\tests\Functional\Admin;

use app\models\Category;
use app\tests\Support\Fixtures\AdminRoleAssignmentFixture;
use app\tests\Support\Fixtures\UserFixture;
use app\tests\Support\FunctionalTester;
use yii\helpers\Url;

final class CategoryCest
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

    public function createUpdateAndDeleteACategory(FunctionalTester $I)
    {
        $I->amOnRoute('admin/category/create');
        $I->submitForm('.category-form form', [
            'Category[name]' => 'Electronics',
            'Category[slug]' => 'electronics',
        ]);
        $I->see('Electronics', 'h1');

        $category = Category::findOne(['slug' => 'electronics']);
        $I->assertNotNull($category);

        // amOnRoute() only prepends the leading '/' on the first call per test
        // (it checks Yii::$app->controller, which the prior request left non-null),
        // so build the absolute URL explicitly for this second navigation.
        $I->amOnPage(Url::to(['/admin/category/update', 'id' => $category->id]));
        $I->submitForm('.category-form form', [
            'Category[name]' => 'Consumer Electronics',
        ]);
        $I->see('Consumer Electronics', 'h1');

        $I->sendAjaxRequest('POST', Url::to(['/admin/category/delete', 'id' => $category->id]));
        $I->seeResponseCodeIsRedirection();
        $I->assertNull(Category::findOne($category->id));
    }
}
