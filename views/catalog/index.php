<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\Category|null $activeCategory */

use yii\helpers\Html;
use yii\widgets\ListView;

$this->title = $activeCategory?->name ?? 'Catalog';
$this->params['breadcrumbs'][] = $activeCategory !== null
    ? ['label' => 'Catalog', 'url' => ['/catalog/index']]
    : $this->title;
if ($activeCategory !== null) {
    $this->params['breadcrumbs'][] = $activeCategory->name;
}
?>
<div class="catalog-index">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h1 class="mb-0"><?= Html::encode($this->title) ?></h1>
        <?php if ($activeCategory !== null): ?>
            <?= Html::a('Show all products', ['/catalog/index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?php endif; ?>
    </div>

    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '_product',
        'layout' => "{items}\n{pager}",
        'itemOptions' => ['tag' => 'div', 'class' => 'col-md-4 mb-4'],
        'options' => ['tag' => 'div', 'class' => 'row'],
        'emptyText' => 'No products available yet.',
    ]) ?>
</div>
