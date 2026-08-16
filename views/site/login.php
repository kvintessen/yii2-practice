<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Login to your account';
$this->params['breadcrumbs'][] = $this->title;
$this->params['meta_description'] = 'Log in to access your account.';
$this->params['meta_keywords'] = 'login, sign in, authentication';
$htmlIcon = <<<HTML
{label}<div class="input-group"><span class="input-group-text" aria-hidden="true">%s</span>{input}</div>{error}{hint}
HTML;
$labelOptions = ['class' => 'form-label fw-semibold small'];
$userIcon = <<<SVG
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.5-7 8-7s8 3 8 7"/></svg>
SVG;
$lockIcon = <<<SVG
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
SVG;
?>
<div class="site-login d-flex align-items-center justify-content-center py-5">
    <div class="card border-0 overflow-hidden login-split-card">
        <div class="row g-0">

            <?= $this->render('_auth-brand-panel', [
                'brandTitle' => 'Welcome<br>Back',
                'brandText' => 'Log in to access your account and manage your data.',
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
                        <p class="text-body-secondary small">Enter your credentials to continue</p>
                    </div>

                    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                    <div class="mb-3">
                        <?= $form->field($model, 'username', [
                            'options' => ['class' => 'mb-0'],
                            'template' => sprintf($htmlIcon, $userIcon),
                            'inputOptions' => [
                                'class' => 'form-control',
                                'placeholder' => 'username',
                                'autofocus' => true,
                            ],
                        ])->textInput()->label('Your Username', $labelOptions) ?>
                    </div>

                    <div class="mb-3">
                        <?= $form->field($model, 'password', [
                            'options' => ['class' => 'mb-0'],
                            'template' => sprintf($htmlIcon, $lockIcon),
                            'inputOptions' => [
                                'class' => 'form-control',
                                'placeholder' => 'Password',
                            ],
                        ])->passwordInput()->label('Your Password', $labelOptions) ?>
                    </div>

                    <div class="mb-4">
                        <?= $form->field($model, 'rememberMe')->checkbox() ?>
                    </div>

                    <div class="d-grid">
                        <?= Html::submitButton(
                            'Login',
                            [
                                'class' => 'btn login-btn btn-lg rounded-3 text-white',
                                'name' => 'login-button',
                            ],
                        ) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                    <div class="text-body-secondary text-center mt-2 small">
                        Don't have an account?
                        <?= Html::a('Sign up', ['site/signup']) ?>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
