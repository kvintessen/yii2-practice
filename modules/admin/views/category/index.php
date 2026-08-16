<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\modules\admin\models\CategorySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\bootstrap5\ActiveForm;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Categories';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-index">
    <?= $this->render('/_nav') ?>

    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Html::a('New Category', ['create'], ['class' => 'btn btn-primary']) ?></p>

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <div class="row g-2 mb-3">
        <div class="col-auto"><?= $form->field($searchModel, 'name')->textInput(['placeholder' => 'Name'])->label(false) ?></div>
        <div class="col-auto"><?= $form->field($searchModel, 'slug')->textInput(['placeholder' => 'Slug'])->label(false) ?></div>
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
            'slug',
            [
                'attribute' => 'parent_id',
                'value' => fn ($model) => $model->parent?->name,
                'label' => 'Parent',
            ],
            ['class' => \yii\grid\ActionColumn::class],
        ],
    ]) ?>
</div>
