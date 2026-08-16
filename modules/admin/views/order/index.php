<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\modules\admin\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\Order;
use yii\bootstrap5\ActiveForm;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Orders';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order-index">
    <?= $this->render('/_nav') ?>

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <div class="row g-2 mb-3">
        <div class="col-auto">
            <?= $form->field($searchModel, 'id')->textInput(['placeholder' => 'Order ID'])->label(false) ?>
        </div>
        <div class="col-auto">
            <?= $form->field($searchModel, 'status')->dropDownList(
                [
                    Order::STATUS_NEW => 'New',
                    Order::STATUS_PAID => 'Paid',
                    Order::STATUS_SHIPPED => 'Shipped',
                    Order::STATUS_DONE => 'Done',
                ],
                ['prompt' => 'Any status'],
            )->label(false) ?>
        </div>
        <div class="col-auto">
            <?= Html::submitButton('Search', ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::a('Reset', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'columns' => [
            'id',
            [
                'label' => 'Customer',
                'value' => fn ($model) => $model->user->username,
            ],
            'status',
            'total',
            'created_at:datetime',
            ['class' => \yii\grid\ActionColumn::class, 'template' => '{view}'],
        ],
    ]) ?>
</div>
