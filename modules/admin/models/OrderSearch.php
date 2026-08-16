<?php

declare(strict_types=1);

namespace app\modules\admin\models;

use app\models\Order;
use yii\data\ActiveDataProvider;

class OrderSearch extends Order
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id'], 'integer'],
            [['status'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => ['id', 'status'],
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Order::find()->with('user');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        $query
            ->andFilterWhere(['id' => $this->id])
            ->andFilterWhere(['status' => $this->status]);

        return $dataProvider;
    }
}
