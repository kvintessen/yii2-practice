<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'My Orders';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped'],
        'emptyText' => 'You haven\'t placed any orders yet.',
        'columns' => [
            'id',
            'status',
            'total',
            'created_at:datetime',
            ['class' => \yii\grid\ActionColumn::class, 'template' => '{view}'],
        ],
    ]) ?>
</div>
