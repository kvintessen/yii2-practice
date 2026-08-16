<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\models\Category;
use app\models\Product;
use app\modules\admin\models\ProductSearch;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ProductController extends Controller
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
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $searchModel = new ProductSearch();
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

    public function actionCreate(): Response|string
    {
        $model = new Product();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->syncCategories($model);

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'categories' => $this->categoryList(),
        ]);
    }

    public function actionUpdate(int $id): Response|string
    {
        $model = $this->findModel($id);
        $model->categoryIds = ArrayHelper::getColumn($model->categories, 'id');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->syncCategories($model);

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'categories' => $this->categoryList(),
        ]);
    }

    public function actionDelete(int $id): Response
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    private function syncCategories(Product $model): void
    {
        $model->unlinkAll('categories', true);

        foreach ($model->categoryIds as $categoryId) {
            $category = Category::findOne($categoryId);

            if ($category !== null) {
                $model->link('categories', $category);
            }
        }
    }

    /**
     * @return string[] category id => name, for the multi-select field.
     */
    private function categoryList(): array
    {
        return ArrayHelper::map(Category::find()->orderBy('name')->all(), 'id', 'name');
    }

    private function findModel(int $id): Product
    {
        $model = Product::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('The requested product does not exist.');
        }

        return $model;
    }
}
