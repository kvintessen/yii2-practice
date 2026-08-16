<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Category $model */

use app\models\Category;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$parentOptions = ArrayHelper::map(
    Category::find()->andWhere(['<>', 'id', (int) $model->id])->orderBy('name')->all(),
    'id',
    'name',
);
?>
<div class="category-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
    <?= $form->field($model, 'slug')->textInput(['maxlength' => 255]) ?>
    <?= $form->field($model, 'parent_id')->dropDownList($parentOptions, ['prompt' => '— none —']) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
