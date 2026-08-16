<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\Product;
use app\tests\Support\FunctionalTester;

final class CatalogCest
{
    private function makeProduct(string $sku, string $status = Product::STATUS_ACTIVE): Product
    {
        $product = new Product([
            'name' => 'Catalog Product ' . $sku,
            'slug' => 'catalog-product-' . strtolower($sku),
            'sku' => $sku,
            'price' => '19.99',
            'stock' => 5,
            'status' => $status,
        ]);
        $product->save();

        return $product;
    }

    public function catalogListsOnlyActiveProducts(FunctionalTester $I)
    {
        $active = $this->makeProduct('CAT-1', Product::STATUS_ACTIVE);
        $draft = $this->makeProduct('CAT-2', Product::STATUS_DRAFT);

        $I->amOnRoute('catalog/index');
        $I->see($active->name);
        $I->dontSee($draft->name);
    }

    public function productPageShowsAddToCartForInStockProduct(FunctionalTester $I)
    {
        $product = $this->makeProduct('CAT-3');

        $I->amOnRoute('catalog/view', ['id' => $product->id]);
        $I->see($product->name, 'h1');
        $I->see($product->sku);
        $I->seeElement('form', ['action' => '/index-test.php?r=cart%2Fadd']);
    }

    public function draftProductIsNotPubliclyViewable(FunctionalTester $I)
    {
        $product = $this->makeProduct('CAT-4', Product::STATUS_DRAFT);

        $I->amOnRoute('catalog/view', ['id' => $product->id]);
        $I->seeResponseCodeIs(404);
    }
}
