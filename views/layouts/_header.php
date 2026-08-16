<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use app\models\Cart;
use yii\bootstrap5\Html;
use yii\bootstrap5\NavBar;

$cart = Cart::findForCurrentVisitor();
$cartItemCount = $cart !== null ? count($cart->items) : 0;

$route = Yii::$app->controller?->route ?? '';

$isActive = static fn (string $r): bool => $route === $r || str_starts_with($route, $r . '/');

$navLinks = [
    [
        'label' => 'Home',
        'url' => ['/site/index'],
        'icon' => '<path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/>',
        'active' => $isActive('site/index'),
    ],
    [
        'label' => 'Catalog',
        'url' => ['/catalog/index'],
        'icon' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'active' => $isActive('catalog'),
    ],
    [
        'label' => 'My Orders',
        'url' => ['/order/index'],
        'icon' => '<path d="M3 7h18l-1.6 11.2a2 2 0 0 1-2 1.8H6.6a2 2 0 0 1-2-1.8L3 7Z"/><path d="M8 7V5a4 4 0 0 1 8 0v2"/>',
        'active' => $isActive('order'),
        'visible' => !Yii::$app->user->isGuest,
    ],
];

?>
<header id="header">
    <?php NavBar::begin(
        [
            'brandLabel' => $this->render('//site/_brand-mark', ['size' => 24]),
            'brandUrl' => Yii::$app->homeUrl,
            'brandOptions' => ['class' => 'navbar-brand storefront-brand'],
            'options' => ['class' => 'navbar-expand-lg storefront-navbar sticky-top'],
            'innerContainerOptions' => ['class' => 'container gap-lg-3'],
        ],
    ) ?>
    <ul class="navbar-nav storefront-nav me-auto mb-2 mb-lg-0">
        <?php foreach ($navLinks as $link): ?>
            <?php if (($link['visible'] ?? true) === false) {
                continue;
            } ?>
            <li class="nav-item">
                <?= Html::a(
                    '<svg class="nav-link-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
                        . $link['icon'] . '</svg>'
                        . '<span>' . Html::encode($link['label']) . '</span>',
                    $link['url'],
                    [
                        'class' => 'nav-link storefront-nav-link' . ($link['active'] ? ' active' : ''),
                        'aria-current' => $link['active'] ? 'page' : null,
                    ],
                ) ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="d-flex align-items-center gap-2 storefront-actions">
        <?= Html::a(
            '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
                . '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>'
                . '<path d="M2.5 3h2l2.7 12.4a2 2 0 0 0 2 1.6h8.2a2 2 0 0 0 2-1.6L21 8H6"/></svg>'
                . ($cartItemCount > 0 ? '<span class="cart-badge">' . $cartItemCount . '</span>' : ''),
            ['/cart/index'],
            [
                'class' => 'btn btn-icon storefront-cart' . ($isActive('cart') ? ' active' : ''),
                'aria-label' => 'Cart (' . $cartItemCount . ' items)',
            ],
        ) ?>

        <?= Html::button(
            '<svg class="theme-icon theme-icon-dark" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>'
            . '<svg class="theme-icon theme-icon-light" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>',
            [
                'id' => 'theme-toggle',
                'class' => 'btn btn-icon',
                'aria-label' => 'Switch to dark mode',
            ],
        ) ?>

        <?php if (Yii::$app->user->isGuest): ?>
            <div class="vr d-none d-lg-block mx-1"></div>
            <?= Html::a('Log in', ['/site/login'], ['class' => 'btn btn-outline-secondary btn-sm storefront-auth-btn']) ?>
            <?= Html::a('Sign up', ['/site/signup'], ['class' => 'btn btn-primary btn-sm storefront-auth-btn storefront-brand-btn']) ?>
        <?php else: ?>
            <div class="dropdown storefront-account">
                <button
                    class="btn btn-icon dropdown-toggle-no-caret"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Account menu"
                >
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><h6 class="dropdown-header"><?= Html::encode(Yii::$app->user->identity?->username ?? '') ?></h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline w-100']) ?>
                        <?= Html::submitButton('Log out', ['class' => 'dropdown-item logout']) ?>
                        <?= Html::endForm() ?>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    <?php NavBar::end() ?>
</header>
