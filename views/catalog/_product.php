<?php

declare(strict_types=1);

/** @var app\models\Product $model */

use yii\helpers\Html;
?>
<div class="card h-100">
    <div class="card-body d-flex flex-column">
        <h5 class="card-title">
            <?= Html::a(Html::encode($model->name), ['/catalog/view', 'id' => $model->id]) ?>
        </h5>
        <p class="card-text text-body-secondary small"><?= Html::encode($model->sku) ?></p>
        <p class="card-text fw-bold"><?= Html::encode($model->price) ?></p>

        <?php if ($model->stock > 0): ?>
            <?= Html::beginForm(['/cart/add'], 'post', ['class' => 'mt-auto d-flex gap-2']) ?>
            <?= Html::hiddenInput('product_id', $model->id) ?>
            <input
                type="number"
                name="quantity"
                value="1"
                min="1"
                max="<?= $model->stock ?>"
                class="form-control form-control-sm"
                style="width: 70px;"
            >
            <?= Html::submitButton('Add to cart', ['class' => 'btn btn-sm btn-primary']) ?>
            <?= Html::endForm() ?>
        <?php else: ?>
            <span class="badge bg-secondary mt-auto align-self-start">Out of stock</span>
        <?php endif; ?>
    </div>
</div>
