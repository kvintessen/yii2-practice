<?php

declare(strict_types=1);

namespace app\services;

use app\models\Cart;

/**
 * Folds the current guest session's cart into a user's cart, so items added
 * before logging in or signing up aren't lost.
 */
final class CartMergeService
{
    /**
     * @param string $guestSessionId Must be captured by the caller *before*
     * logging in — yii\web\User::login() regenerates the session id, so
     * reading it fresh here would miss the guest's actual cart.
     */
    public function mergeGuestCartIntoUser(string $guestSessionId, int $userId): void
    {
        $guestCart = Cart::findOne(['session_id' => $guestSessionId]);

        if ($guestCart === null) {
            return;
        }

        $userCart = Cart::findOrCreateForUser($userId);

        if ($guestCart->id === $userCart->id) {
            return;
        }

        $guestCart->mergeInto($userCart);
    }
}
