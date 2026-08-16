<?php

declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * A snapshot of a purchased line item: product_name/unit_price are copied
 * from the product at order time so the order stays accurate even if the
 * product is later renamed, repriced, or deleted.
 *
 * @property int $id
 * @property int $order_id
 * @property int|null $product_id
 * @property string $product_name
 * @property string $unit_price
 * @property int $quantity
 * @property string $line_total
 * @property int $created_at
 *
 * @property-read Order $order
 * @property-read Product|null $product
 */
class OrderItem extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%order_item}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['order_id', 'product_name', 'unit_price', 'quantity', 'line_total'], 'required'],
            [['order_id', 'product_id'], 'integer'],
            [['quantity'], 'integer', 'min' => 1],
            [['unit_price', 'line_total'], 'number', 'min' => 0],
            [['product_name'], 'string', 'max' => 255],
        ];
    }

    public function getOrder(): ActiveQuery
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    public function getProduct(): ActiveQuery
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }
}
