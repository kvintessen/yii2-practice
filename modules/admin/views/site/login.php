<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Admin sign in';
$this->params['meta_description'] = 'Sign in to the admin panel.';

$labelOptions = ['class' => 'form-label fw-semibold small'];
?>
<div class="d-flex align-items-center justify-content-center py-5">
    <div class="card border-0 shadow-sm" style="width: 100%; max-width: 380px;">
        <div class="card-body p-4 p-lg-5">
            <div class="text-center mb-4">
                <div class="brand-mark justify-content-center mb-3">
                    <?= $this->render('//site/_brand-mark', ['size' => 26]) ?>
                </div>
                <h1 class="h4 fw-bold mb-1"><?= Html::encode($this->title) ?></h1>
                <p class="text-body-secondary small mb-0">Restricted to admin accounts only</p>
            </div>

            <?php $form = ActiveForm::begin(['id' => 'admin-login-form']); ?>

            <div class="mb-3">
                <?= $form->field($model, 'username')->textInput([
                    'autofocus' => true,
                    'placeholder' => 'username',
                ])->label('Username', $labelOptions) ?>
            </div>

            <div class="mb-3">
                <?= $form->field($model, 'password')->passwordInput([
                    'placeholder' => 'Password',
                ])->label('Password', $labelOptions) ?>
            </div>

            <div class="mb-4">
                <?= $form->field($model, 'rememberMe')->checkbox() ?>
            </div>

            <div class="d-grid">
                <?= Html::submitButton('Sign in', ['class' => 'btn btn-primary btn-lg']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
