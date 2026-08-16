<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Category[] $categories */
/** @var app\models\Product[] $newArrivals */

use yii\helpers\Html;

$this->title = 'Home';
$this->params['meta_description'] = 'Browse electronics, clothing, books, and home goods.';
$this->params['meta_keywords'] = 'shop, catalog, online store';

$categoryIcon = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" '
    . 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 7 9-5 9 5-9 5-9-5Z"/>'
    . '<path d="m3 7 9 5 9-5M3 12l9 5 9-5M3 17l9 5 9-5"/></svg>';
?>
<div class="site-index">
    <section class="home-hero rounded-4 p-4 p-md-5 mb-5 text-white">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h1 class="display-6 fw-bold mb-3">Everything you need, in one place</h1>
                <p class="lead mb-4 home-hero-subtext">
                    Browse electronics, clothing, books, and home goods — all in
                    <?= Html::encode(Yii::$app->name) ?>.
                </p>
                <?= Html::a('Browse the catalog', ['/catalog/index'], ['class' => 'btn btn-light btn-lg px-4 fw-semibold']) ?>
            </div>
            <div class="col-lg-5 d-none d-lg-flex justify-content-center">
                <svg width="140" height="140" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="home-hero-icon" aria-hidden="true">
                    <path d="M6 7h12l1.5 13.2a1.5 1.5 0 0 1-1.5 1.8H6a1.5 1.5 0 0 1-1.5-1.8L6 7Z"/>
                    <path d="M9 10V6a3 3 0 0 1 6 0v4"/>
                </svg>
            </div>
        </div>
    </section>

    <?php if ($categories !== []): ?>
        <section class="mb-5">
            <h2 class="h4 fw-semibold mb-3">Shop by category</h2>
            <div class="row g-3">
                <?php foreach ($categories as $category): ?>
                    <div class="col-6 col-md-3">
                        <?= Html::a(
                            '<span class="category-card-icon">' . $categoryIcon . '</span>'
                                . '<span class="category-card-name">' . Html::encode($category->name) . '</span>',
                            ['/catalog/index', 'category' => $category->id],
                            ['class' => 'category-card'],
                        ) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($newArrivals !== []): ?>
        <section class="mb-5">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h4 fw-semibold mb-0">New arrivals</h2>
                <?= Html::a('View all products', ['/catalog/index'], ['class' => 'fw-medium']) ?>
            </div>
            <div class="row">
                <?php foreach ($newArrivals as $product): ?>
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <?= $this->render('//catalog/_product', ['model' => $product]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="row g-4 text-center home-benefits">
        <div class="col-md-4">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-2" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            <h3 class="h6 fw-semibold">Wide catalog</h3>
            <p class="text-body-secondary small mb-0">Products across electronics, clothing, books, and home goods.</p>
        </div>
        <div class="col-md-4">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-2" aria-hidden="true"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.5 3h2l2.7 12.4a2 2 0 0 0 2 1.6h8.2a2 2 0 0 0 2-1.6L21 8H6"/></svg>
            <h3 class="h6 fw-semibold">Simple checkout</h3>
            <p class="text-body-secondary small mb-0">Add items to your cart and check out in a few clicks.</p>
        </div>
        <div class="col-md-4">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-2" aria-hidden="true"><path d="M3 7h18l-1.6 11.2a2 2 0 0 1-2 1.8H6.6a2 2 0 0 1-2-1.8L3 7Z"/><path d="M8 7V5a4 4 0 0 1 8 0v2"/></svg>
            <h3 class="h6 fw-semibold">Track your orders</h3>
            <p class="text-body-secondary small mb-0">Sign up to view your order history any time.</p>
        </div>
    </section>
</div>
