<?php

declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $cart_id
 * @property int $product_id
 * @property int $quantity
 * @property int $created_at
 * @property int $updated_at
 *
 * @property-read Cart $cart
 * @property-read Product $product
 */
class CartItem extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%cart_item}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['cart_id', 'product_id', 'quantity'], 'required'],
            [['cart_id', 'product_id'], 'integer'],
            [['quantity'], 'integer', 'min' => 1],
        ];
    }

    public function getCart(): ActiveQuery
    {
        return $this->hasOne(Cart::class, ['id' => 'cart_id']);
    }

    public function getProduct(): ActiveQuery
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }
}
