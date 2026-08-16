<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Product $product */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = $product->name;
$this->params['breadcrumbs'][] = ['label' => 'Catalog', 'url' => ['/catalog/index']];
$this->params['breadcrumbs'][] = $this->title;

$categories = ArrayHelper::getColumn($product->categories, 'name');
?>
<div class="catalog-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <p class="text-body-secondary">SKU: <?= Html::encode($product->sku) ?></p>
    <p class="fs-4 fw-bold"><?= Html::encode($product->price) ?></p>

    <?php if ($product->description !== null && $product->description !== ''): ?>
        <p><?= nl2br(Html::encode($product->description)) ?></p>
    <?php endif; ?>

    <?php if ($categories !== []): ?>
        <p>Categories: <?= Html::encode(implode(', ', $categories)) ?></p>
    <?php endif; ?>

    <?php if ($product->stock > 0): ?>
        <p class="text-success">In stock: <?= $product->stock ?></p>
        <?= Html::beginForm(['/cart/add'], 'post', ['class' => 'd-flex gap-2']) ?>
        <?= Html::hiddenInput('product_id', $product->id) ?>
        <input
            type="number"
            name="quantity"
            value="1"
            min="1"
            max="<?= $product->stock ?>"
            class="form-control"
            style="width: 100px;"
        >
        <?= Html::submitButton('Add to cart', ['class' => 'btn btn-primary']) ?>
        <?= Html::endForm() ?>
    <?php else: ?>
        <span class="badge bg-secondary">Out of stock</span>
    <?php endif; ?>
</div>
