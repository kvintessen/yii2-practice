<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\models\Order;
use app\modules\admin\models\OrderSearch;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class OrderController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'update-status' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionUpdateStatus(int $id): Response
    {
        $order = $this->findModel($id);
        $order->status = (string) Yii::$app->request->post('status');
        $order->save();

        return $this->redirect(['view', 'id' => $order->id]);
    }

    private function findModel(int $id): Order
    {
        $model = Order::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('The requested order does not exist.');
        }

        return $model;
    }
}
