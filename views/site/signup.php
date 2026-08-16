<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var app\models\SignupForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Create an account';
$this->params['breadcrumbs'][] = $this->title;
$this->params['meta_description'] = 'Create a new account.';
$this->params['meta_keywords'] = 'signup, register, sign up, registration';
$htmlIcon = <<<HTML
{label}<div class="input-group"><span class="input-group-text" aria-hidden="true">%s</span>{input}</div>{error}{hint}
HTML;
$labelOptions = ['class' => 'form-label fw-semibold small'];
$userIcon = <<<SVG
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.5-7 8-7s8 3 8 7"/></svg>
SVG;
$emailIcon = <<<SVG
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
SVG;
$lockIcon = <<<SVG
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
SVG;
?>
<div class="site-signup d-flex align-items-center justify-content-center py-5">
    <div class="card border-0 overflow-hidden login-split-card">
        <div class="row g-0">

            <?= $this->render('_auth-brand-panel', [
                'brandTitle' => 'Join<br>Us',
                'brandText' => 'Create an account to get started.',
            ]) ?>

            <!-- Form panel -->
            <div class="col-md-7">
                <div class="p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <!-- Mobile-only brand mark -->
                        <div class="d-md-none mb-3">
                            <span class="brand-mark login-mobile-logo">
                                <?= $this->render('_brand-mark', ['size' => 22]) ?>
                            </span>
                        </div>
                        <h1 class="h3 fw-bold mb-1"><?= Html::encode($this->title) ?></h1>
                        <p class="text-body-secondary small">It only takes a minute</p>
                    </div>

                    <?php $form = ActiveForm::begin(['id' => 'signup-form']); ?>

                    <div class="mb-3">
                        <?= $form->field($model, 'username', [
                            'options' => ['class' => 'mb-0'],
                            'template' => sprintf($htmlIcon, $userIcon),
                            'inputOptions' => [
                                'class' => 'form-control',
                                'placeholder' => 'username',
                                'autofocus' => true,
                            ],
                        ])->textInput()->label('Username', $labelOptions) ?>
                    </div>

                    <div class="mb-3">
                        <?= $form->field($model, 'email', [
                            'options' => ['class' => 'mb-0'],
                            'template' => sprintf($htmlIcon, $emailIcon),
                            'inputOptions' => [
                                'class' => 'form-control',
                                'placeholder' => 'email@example.com',
                            ],
                        ])->textInput()->label('Email', $labelOptions) ?>
                    </div>

                    <div class="mb-3">
                        <?= $form->field($model, 'password', [
                            'options' => ['class' => 'mb-0'],
                            'template' => sprintf($htmlIcon, $lockIcon),
                            'inputOptions' => [
                                'class' => 'form-control',
                                'placeholder' => 'Password',
                            ],
                        ])->passwordInput()->label('Password', $labelOptions) ?>
                    </div>

                    <div class="mb-4">
                        <?= $form->field($model, 'passwordRepeat', [
                            'options' => ['class' => 'mb-0'],
                            'template' => sprintf($htmlIcon, $lockIcon),
                            'inputOptions' => [
                                'class' => 'form-control',
                                'placeholder' => 'Repeat password',
                            ],
                        ])->passwordInput()->label('Repeat Password', $labelOptions) ?>
                    </div>

                    <div class="d-grid">
                        <?= Html::submitButton(
                            'Sign up',
                            [
                                'class' => 'btn login-btn btn-lg rounded-3 text-white',
                                'name' => 'signup-button',
                            ],
                        ) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                    <div class="text-body-secondary text-center mt-3 small">
                        Already have an account?
                        <?= Html::a('Log in', ['site/login']) ?>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
