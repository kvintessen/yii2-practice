<?php

declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property string $status
 * @property string $total
 * @property int $created_at
 * @property int $updated_at
 *
 * @property-read OrderItem[] $items
 * @property-read User $user
 * @property-read Payment[] $payments
 */
class Order extends ActiveRecord
{
    public const STATUS_NEW = 'new';
    public const STATUS_PAID = 'paid';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DONE = 'done';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%order}}';
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
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['status'], 'default', 'value' => self::STATUS_NEW],
            [['status'], 'in', 'range' => [
                self::STATUS_NEW,
                self::STATUS_PAID,
                self::STATUS_SHIPPED,
                self::STATUS_DONE,
            ]],
            [['total'], 'number', 'min' => 0],
        ];
    }

    public function getItems(): ActiveQuery
    {
        return $this->hasMany(OrderItem::class, ['order_id' => 'id']);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getPayments(): ActiveQuery
    {
        return $this->hasMany(Payment::class, ['order_id' => 'id']);
    }
}
