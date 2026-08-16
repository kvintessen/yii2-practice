<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Product $model */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-outline-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this product?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'slug',
            'sku',
            'price',
            'stock',
            'status',
            'description:ntext',
            [
                'label' => 'Categories',
                'value' => implode(', ', ArrayHelper::getColumn($model->categories, 'name')) ?: '—',
            ],
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>
</div>
