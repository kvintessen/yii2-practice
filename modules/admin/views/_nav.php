<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\helpers\Html;

$route = Yii::$app->controller?->route ?? '';
$isActive = static fn (string $prefix): bool => str_starts_with($route, $prefix);

$items = [
    ['label' => 'Categories', 'url' => ['/admin/category/index'], 'active' => $isActive('admin/category')],
    ['label' => 'Products', 'url' => ['/admin/product/index'], 'active' => $isActive('admin/product')],
    ['label' => 'Orders', 'url' => ['/admin/order/index'], 'active' => $isActive('admin/order')],
];
?>
<div class="admin-sidebar">
    <button
        class="btn btn-outline-secondary btn-sm d-md-none w-100 mb-2 d-flex justify-content-between align-items-center"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#adminSidebarNav"
        aria-expanded="false"
        aria-controls="adminSidebarNav"
    >
        <span>Admin menu</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="m6 9 6 6 6-6"/>
        </svg>
    </button>
    <nav class="collapse d-md-block" id="adminSidebarNav" aria-label="Admin sections">
        <ul class="list-unstyled admin-sidebar-list mb-0">
            <?php foreach ($items as $item): ?>
                <li>
                    <?= Html::a(
                        Html::encode($item['label']),
                        $item['url'],
                        [
                            'class' => 'admin-sidebar-link' . ($item['active'] ? ' active' : ''),
                            'aria-current' => $item['active'] ? 'page' : null,
                        ],
                    ) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</div>
