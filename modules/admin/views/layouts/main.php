<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $content */

$this->beginContent('@app/views/layouts/main.php');
?>
<div class="row g-4">
    <div class="col-md-2">
        <?= $this->render('/_nav') ?>
    </div>
    <div class="col-md-10">
        <?= $content ?>
    </div>
</div>
<?php $this->endContent(); ?>
