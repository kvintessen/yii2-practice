<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\helpers\Html;
use yii\widgets\ListView;

$this->title = 'Catalog';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="catalog-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '_product',
        'layout' => "{items}\n{pager}",
        'itemOptions' => ['tag' => 'div', 'class' => 'col-md-4 mb-4'],
        'options' => ['tag' => 'div', 'class' => 'row'],
        'emptyText' => 'No products available yet.',
    ]) ?>
</div>
