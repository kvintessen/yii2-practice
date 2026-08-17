<?php

declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * A single payment attempt against an order. An order can have more than one
 * row here (cancelled and retried with a different provider), which is why
 * this isn't just a column on Order.
 *
 * @property int $id
 * @property int $order_id
 * @property string $provider
 * @property string|null $external_id
 * @property string $status
 * @property string $amount
 * @property string $currency
 * @property string|null $confirmation_url
 * @property string|null $raw_payload
 * @property int $created_at
 * @property int $updated_at
 *
 * @property-read Order $order
 */
class Payment extends ActiveRecord
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_FAILED = 'failed';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%payment}}';
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
            [['order_id', 'provider', 'amount'], 'required'],
            [['order_id'], 'integer'],
            [['provider'], 'string', 'max' => 32],
            [['external_id'], 'string', 'max' => 64],
            [['external_id'], 'unique', 'targetAttribute' => ['provider', 'external_id']],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['status'], 'in', 'range' => [
                self::STATUS_PENDING,
                self::STATUS_SUCCEEDED,
                self::STATUS_CANCELED,
                self::STATUS_FAILED,
            ]],
            [['amount'], 'number', 'min' => 0],
            [['currency'], 'default', 'value' => 'RUB'],
            [['currency'], 'string', 'max' => 3],
            [['confirmation_url'], 'string', 'max' => 512],
            [['raw_payload'], 'string'],
        ];
    }

    public function getOrder(): ActiveQuery
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    /**
     * Normalized to exactly 2 decimals with a '.' separator — the format
     * every gateway's API/signature expects, regardless of how the decimal
     * column happens to have stringified this particular value.
     */
    public function getFormattedAmount(): string
    {
        return number_format((float) $this->amount, 2, '.', '');
    }
}
