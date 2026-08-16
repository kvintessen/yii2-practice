<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Category;
use app\models\Product;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class CatalogController extends Controller
{
    public function actionIndex(?int $category = null): string
    {
        $query = Product::find()->where(['status' => Product::STATUS_ACTIVE]);

        $activeCategory = null;
        if ($category !== null) {
            $activeCategory = Category::findOne($category);
            $query->joinWith('categories')->andWhere(['category.id' => $category]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy('name'),
            'pagination' => ['pageSize' => 12],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'activeCategory' => $activeCategory,
        ]);
    }

    public function actionView(int $id): string
    {
        $product = Product::find()
            ->where(['id' => $id, 'status' => Product::STATUS_ACTIVE])
            ->one();

        if ($product === null) {
            throw new NotFoundHttpException('The requested product does not exist.');
        }

        return $this->render('view', [
            'product' => $product,
        ]);
    }
}
