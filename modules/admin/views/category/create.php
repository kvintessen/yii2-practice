<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Category $model */

use yii\helpers\Html;

$this->title = 'New Category';
$this->params['breadcrumbs'][] = ['label' => 'Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model]) ?>
</div>
