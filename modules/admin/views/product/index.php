<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\modules\admin\models\ProductSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\Product;
use yii\bootstrap5\ActiveForm;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Products';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Html::a('New Product', ['create'], ['class' => 'btn btn-primary']) ?></p>

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <div class="row g-2 mb-3">
        <div class="col-auto"><?= $form->field($searchModel, 'name')->textInput(['placeholder' => 'Name'])->label(false) ?></div>
        <div class="col-auto"><?= $form->field($searchModel, 'sku')->textInput(['placeholder' => 'SKU'])->label(false) ?></div>
        <div class="col-auto">
            <?= $form->field($searchModel, 'status')->dropDownList(
                [Product::STATUS_DRAFT => 'Draft', Product::STATUS_ACTIVE => 'Active'],
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
            'name',
            'sku',
            'price',
            'status',
            ['class' => \yii\grid\ActionColumn::class],
        ],
    ]) ?>
</div>
