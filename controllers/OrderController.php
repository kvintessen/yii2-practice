<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Order;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class OrderController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionView(int $id): string
    {
        $order = Order::findOne($id);

        if ($order === null) {
            throw new NotFoundHttpException('The requested order does not exist.');
        }

        if ($order->user_id !== (int) Yii::$app->user->id) {
            throw new ForbiddenHttpException('You are not allowed to view this order.');
        }

        return $this->render('view', [
            'order' => $order,
        ]);
    }
}
