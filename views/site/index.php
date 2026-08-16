<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Home';
$this->params['meta_description'] = 'A Yii2 application with user registration and login.';
$this->params['meta_keywords'] = 'yii, yii2, php, framework';
?>
<div class="site-index d-flex align-items-center justify-content-center text-center py-5">
    <div class="site-index-content mx-auto">
        <?php if (Yii::$app->user->isGuest): ?>
            <h1 class="display-6 fw-semibold mb-3">Welcome</h1>
            <p class="text-body-secondary mb-4">
                Log in to your account, or create a new one to get started.
            </p>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <?= Html::a('Log in', ['site/login'], ['class' => 'btn btn-primary btn-lg px-4']) ?>
                <?= Html::a('Sign up', ['site/signup'], ['class' => 'btn btn-outline-primary btn-lg px-4']) ?>
            </div>
        <?php else: ?>
            <h1 class="display-6 fw-semibold mb-3">
                Welcome back, <?= Html::encode(Yii::$app->user->identity->username) ?>
            </h1>
            <p class="text-body-secondary mb-4">
                You are logged in.
            </p>
            <?= Html::beginForm(['site/logout'], 'post') ?>
                <?= Html::submitButton('Logout', ['class' => 'btn btn-outline-secondary btn-lg px-4']) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
    </div>
</div>
