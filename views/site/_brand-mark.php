<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var int $size icon size in px */

use yii\bootstrap5\Html;

$size ??= 28;
?>
<svg class="brand-mark-icon" width="<?= $size ?>" height="<?= $size ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3Z"/><path d="m9 12 2 2 4-4"/></svg>
<span class="brand-mark-text"><?= Html::encode(Yii::$app->name) ?></span>
