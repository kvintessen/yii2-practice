<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Product $model */
/** @var string[] $categories */

use yii\helpers\Html;

$this->title = 'Edit Product: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Edit';
?>
<div class="product-update">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model, 'categories' => $categories]) ?>
</div>
