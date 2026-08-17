<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'name' => 'TrustCart',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'container' => [
        'definitions' => [
            // ListView/GridView default to the plain yii\widgets\LinkPager,
            // which renders <li>/<a> with no page-item/page-link classes —
            // the Bootstrap 5 pagination CSS never applies, so pages render
            // as bare links crammed together. Swap in the Bootstrap-aware
            // pager everywhere instead of per-view.
            \yii\widgets\LinkPager::class => \yii\bootstrap5\LinkPager::class,
        ],
        'singletons' => [
            \yii\mail\MailerInterface::class => [
                'class' => \yii\symfonymailer\Mailer::class,
                // send all mails to a file by default.
                'useFileTransport' => true,
                'viewPath' => '@app/mail',
            ],
            // 'fake' is registered alongside the real gateway(s) so the
            // checkout picker and the callback flow are exercisable without
            // real provider credentials — see services/Payment/FakeGateway.php.
            \app\services\Payment\PaymentGatewayRegistry::class => static function () use ($params) {
                return new \app\services\Payment\PaymentGatewayRegistry([
                    Yii::createObject([
                        'class' => \app\services\Payment\YooKassaGateway::class,
                        '__construct()' => [$params['yookassaShopId'], $params['yookassaSecretKey']],
                    ]),
                    Yii::createObject(\app\services\Payment\FakeGateway::class),
                ]);
            },
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [
        'admin' => \app\modules\admin\AdminModule::class,
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'cyyutjjv_Lv7UNXxerVrPvTy-XuLGRla',
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => true,
        ],
        // separate identity/session from the storefront 'user' component so
        // logging into the shop never grants admin access (and vice versa),
        // even for an account that holds the 'admin' RBAC role
        'adminUser' => [
            'class' => \yii\web\User::class,
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => true,
            'loginUrl' => ['/admin/site/login'],
            'idParam' => '__admin_id',
            'authTimeoutParam' => '__admin_expire',
            'identityCookie' => ['name' => '_adminIdentity', 'httpOnly' => true],
        ],
        'authManager' => [
            'class' => \yii\rbac\DbManager::class,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => \yii\mail\MailerInterface::class,
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
                '' => 'site/index',
                'login' => 'site/login',
                'logout' => 'site/logout',
                'signup' => 'site/signup',

                'catalog/category/<category:\d+>' => 'catalog/index',
                'catalog/product/<id:\d+>' => 'catalog/view',
                'catalog' => 'catalog/index',

                'cart' => 'cart/index',
                'cart/add' => 'cart/add',
                'cart/update-quantity' => 'cart/update-quantity',
                'cart/remove' => 'cart/remove',
                'cart/checkout' => 'cart/checkout',
                'cart/place-order' => 'cart/place-order',

                'orders' => 'order/index',
                'orders/<id:\d+>' => 'order/view',

                'orders/<orderId:\d+>/pay' => 'payment/pay',
                'payment/create' => 'payment/create',
                'payment/<provider:[\w-]+>/callback' => 'payment/callback',
                'payment/<provider:[\w-]+>/return' => 'payment/return',

                'admin' => 'admin/category/index',
                'admin/login' => 'admin/site/login',
                'admin/logout' => 'admin/site/logout',
                'admin/categories' => 'admin/category/index',
                'admin/categories/create' => 'admin/category/create',
                'admin/categories/<id:\d+>/update' => 'admin/category/update',
                'admin/categories/<id:\d+>/delete' => 'admin/category/delete',
                'admin/categories/<id:\d+>' => 'admin/category/view',

                'admin/products' => 'admin/product/index',
                'admin/products/create' => 'admin/product/create',
                'admin/products/<id:\d+>/update' => 'admin/product/update',
                'admin/products/<id:\d+>/delete' => 'admin/product/delete',
                'admin/products/<id:\d+>' => 'admin/product/view',

                'admin/orders' => 'admin/order/index',
                'admin/orders/<id:\d+>/status' => 'admin/order/update-status',
                'admin/orders/<id:\d+>' => 'admin/order/view',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    // dev-tool routes, kept separate from the app's explicit rule list above
    $config['components']['urlManager']['rules'] = array_merge(
        $config['components']['urlManager']['rules'],
        [
            'debug' => 'debug/default/index',
            'debug/<controller:[\w-]+>/<action:[\w-]+>' => 'debug/<controller>/<action>',
            'gii' => 'gii/default/index',
            'gii/<controller:[\w-]+>/<action:[\w-]+>' => 'gii/<controller>/<action>',
        ],
    );
}

return $config;
