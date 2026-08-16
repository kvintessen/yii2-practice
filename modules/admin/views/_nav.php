<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\helpers\Html;

?>
<ul class="nav nav-pills mb-3">
    <li class="nav-item"><?= Html::a('Categories', ['/admin/category/index'], ['class' => 'nav-link']) ?></li>
    <li class="nav-item"><?= Html::a('Products', ['/admin/product/index'], ['class' => 'nav-link']) ?></li>
    <li class="nav-item"><?= Html::a('Orders', ['/admin/order/index'], ['class' => 'nav-link']) ?></li>
</ul>
