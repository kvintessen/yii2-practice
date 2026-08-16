<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $brandTitle raw HTML heading (line breaks allowed) */
/** @var string $brandText plain text, will be encoded */

use yii\bootstrap5\Html;
?>
<div class="col-md-5 d-none d-md-flex login-brand-panel text-white">
    <div class="d-flex flex-column justify-content-between p-4 p-lg-5 w-100">
        <div class="brand-mark"><?= $this->render('_brand-mark') ?></div>
        <div>
            <h2 class="fw-bold mb-3 login-brand-title"><?= $brandTitle ?></h2>
            <p class="opacity-75 mb-0 login-brand-text"><?= Html::encode($brandText) ?></p>
        </div>
    </div>
</div>
