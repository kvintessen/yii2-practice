<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\Category;
use app\models\Product;

final class ProductTest extends \Codeception\Test\Unit
{
    public function testValidationRequiresCoreFields()
    {
        $product = new Product();

        verify($product->validate())->false();
        verify($product->errors)->arrayHasKey('name');
        verify($product->errors)->arrayHasKey('slug');
        verify($product->errors)->arrayHasKey('price');
        verify($product->errors)->arrayHasKey('sku');
    }

    public function testStatusDefaultsToDraft()
    {
        $product = new Product([
            'name' => 'Phone X',
            'slug' => 'phone-x',
            'price' => '199.99',
            'sku' => 'PHONE-X',
        ]);

        verify($product->validate())->true();
        verify($product->status)->equals(Product::STATUS_DRAFT);
    }

    public function testSlugAndSkuMustBeUnique()
    {
        $original = new Product([
            'name' => 'Phone X',
            'slug' => 'phone-x',
            'price' => '199.99',
            'sku' => 'PHONE-X',
        ]);
        verify($original->save())->true();

        $duplicate = new Product([
            'name' => 'Phone X clone',
            'slug' => 'phone-x',
            'price' => '99.99',
            'sku' => 'PHONE-X-2',
        ]);

        verify($duplicate->validate())->false();
        verify($duplicate->errors)->arrayHasKey('slug');
    }

    public function testCategoriesRelation()
    {
        $category = new Category(['name' => 'Phones', 'slug' => 'phones']);
        verify($category->save())->true();

        $product = new Product([
            'name' => 'Phone X',
            'slug' => 'phone-x',
            'price' => '199.99',
            'sku' => 'PHONE-X',
        ]);
        verify($product->save())->true();

        $product->link('categories', $category);

        $fresh = Product::findOne($product->id);

        verify(count($fresh->categories))->equals(1);
        verify($fresh->categories[0]->id)->equals($category->id);
    }
}
