<?php

declare(strict_types=1);

namespace app\modules\admin\models;

use app\models\Product;
use yii\data\ActiveDataProvider;

class ProductSearch extends Product
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id'], 'integer'],
            [['name', 'slug', 'sku', 'status'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => ['id', 'name', 'slug', 'sku', 'status'],
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Product::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['name' => SORT_ASC],
            ],
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        $query
            ->andFilterWhere(['id' => $this->id])
            ->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'slug', $this->slug])
            ->andFilterWhere(['like', 'sku', $this->sku]);

        return $dataProvider;
    }
}
