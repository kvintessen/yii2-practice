<?php

declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $price
 * @property string $sku
 * @property string $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property-read Category[] $categories
 */
class Product extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';

    /** @var int[] Category ids to assign on save; not a DB column. */
    public array $categoryIds = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%product}}';
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
            [['name', 'slug', 'price', 'sku'], 'required'],
            [['description'], 'string'],
            [['price'], 'number', 'min' => 0],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_ACTIVE]],
            [['name', 'slug'], 'string', 'max' => 255],
            [['sku'], 'string', 'max' => 64],
            [['slug'], 'unique'],
            [['sku'], 'unique'],
            [['slug'], 'match', 'pattern' => '/^[a-z0-9-]+$/', 'message' => 'Slug may only contain lowercase letters, numbers, and dashes.'],
            [['categoryIds'], 'each', 'rule' => ['integer']],
        ];
    }

    public function getCategories(): ActiveQuery
    {
        return $this->hasMany(Category::class, ['id' => 'category_id'])
            ->viaTable('{{%product_category}}', ['product_id' => 'id']);
    }
}
