<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Product $model */
/** @var string[] $categories */

use yii\helpers\Html;

$this->title = 'New Product';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model, 'categories' => $categories]) ?>
</div>
