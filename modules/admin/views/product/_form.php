<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Product $model */
/** @var string[] $categories */

use app\models\Product;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

?>
<div class="product-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
    <?= $form->field($model, 'slug')->textInput(['maxlength' => 255]) ?>
    <?= $form->field($model, 'sku')->textInput(['maxlength' => 64]) ?>
    <?= $form->field($model, 'price')->textInput() ?>
    <?= $form->field($model, 'stock')->textInput(['type' => 'number', 'min' => 0]) ?>
    <?= $form->field($model, 'status')->dropDownList([
        Product::STATUS_DRAFT => 'Draft',
        Product::STATUS_ACTIVE => 'Active',
    ]) ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>
    <?= $form->field($model, 'categoryIds')->checkboxList($categories)->label('Categories') ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
