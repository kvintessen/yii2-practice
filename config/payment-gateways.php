<?php

declare(strict_types=1);

/**
 * Builds the PaymentGatewayRegistry singleton. Shared by config/web.php and
 * config/test.php so registering a new gateway means editing this list
 * once, not remembering to keep two copies of it in sync.
 *
 * Returns a closure rather than the registry itself: it's only invoked by
 * the DI container the first time something actually asks for
 * PaymentGatewayRegistry, by which point Yii::$app is fully built and
 * Yii::$app->params is safe to read.
 */
return static function (): \app\services\Payment\PaymentGatewayRegistry {
    $params = Yii::$app->params;

    return new \app\services\Payment\PaymentGatewayRegistry([
        Yii::createObject([
            'class' => \app\services\Payment\YooKassaGateway::class,
            '__construct()' => [$params['yookassaShopId'], $params['yookassaSecretKey']],
        ]),
        Yii::createObject([
            'class' => \app\services\Payment\RobokassaGateway::class,
            '__construct()' => [
                $params['robokassaMerchantLogin'],
                $params['robokassaPassword1'],
                $params['robokassaPassword2'],
                $params['robokassaTestMode'],
            ],
        ]),
        // Registered alongside the real gateways so the checkout picker and
        // the callback flow are exercisable without real provider
        // credentials — see services/Payment/FakeGateway.php.
        Yii::createObject(\app\services\Payment\FakeGateway::class),
    ]);
};
