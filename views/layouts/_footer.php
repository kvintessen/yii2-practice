<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\helpers\Html;

?>
<footer id="footer" class="mt-auto py-3 bg-body-tertiary">
    <div class="container">
        <div class="row text-body-secondary">
            <div class="col-12 text-center">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></div>
        </div>
    </div>
</footer>
