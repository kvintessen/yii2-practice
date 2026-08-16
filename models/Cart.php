<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $session_id
 * @property int $created_at
 * @property int $updated_at
 *
 * @property-read CartItem[] $items
 */
class Cart extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%cart}}';
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
            [['user_id'], 'integer'],
            [['session_id'], 'string', 'max' => 64],
        ];
    }

    public function getItems(): ActiveQuery
    {
        return $this->hasMany(CartItem::class, ['cart_id' => 'id']);
    }

    public static function findOrCreateForUser(int $userId): self
    {
        return self::findOne(['user_id' => $userId]) ?? self::create(['user_id' => $userId]);
    }

    public static function findOrCreateForSession(string $sessionId): self
    {
        return self::findOne(['session_id' => $sessionId]) ?? self::create(['session_id' => $sessionId]);
    }

    /**
     * Resolves the current visitor's cart, creating one if none exists yet.
     * Use for actions that add to the cart; prefer findForCurrentVisitor()
     * for read-only access so browsing alone doesn't create empty carts.
     */
    public static function findOrCreateForCurrentVisitor(): self
    {
        if (!Yii::$app->user->isGuest) {
            return self::findOrCreateForUser((int) Yii::$app->user->id);
        }

        return self::findOrCreateForSession(Yii::$app->session->getId());
    }

    public static function findForCurrentVisitor(): self|null
    {
        if (!Yii::$app->user->isGuest) {
            return self::findOne(['user_id' => Yii::$app->user->id]);
        }

        return self::findOne(['session_id' => Yii::$app->session->getId()]);
    }

    /**
     * Adds a product to the cart, or increases its quantity if already present.
     */
    public function addItem(Product $product, int $quantity = 1): CartItem
    {
        $item = CartItem::findOne(['cart_id' => $this->id, 'product_id' => $product->id]) ?? new CartItem([
            'cart_id' => $this->id,
            'product_id' => $product->id,
            'quantity' => 0,
        ]);

        $item->quantity += $quantity;
        $item->save();

        return $item;
    }

    /**
     * Moves this cart's items into $target (merging quantities for shared
     * products), then removes this cart. Used to fold a guest cart into the
     * user's cart on login.
     */
    public function mergeInto(self $target): void
    {
        foreach ($this->items as $item) {
            $existing = CartItem::findOne(['cart_id' => $target->id, 'product_id' => $item->product_id]);

            if ($existing !== null) {
                $existing->quantity += $item->quantity;
                $existing->save();
                $item->delete();
            } else {
                $item->cart_id = $target->id;
                $item->save();
            }
        }

        $this->delete();
    }

    public function getTotal(): float
    {
        $total = 0.0;

        foreach ($this->items as $item) {
            $total += (float) $item->product->price * $item->quantity;
        }

        return round($total, 2);
    }

    private static function create(array $attributes): self
    {
        $cart = new self($attributes);
        $cart->save();

        return $cart;
    }
}
