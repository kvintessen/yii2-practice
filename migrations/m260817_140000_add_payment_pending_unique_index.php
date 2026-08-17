<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Enforces at most one `pending` payment per order at the database level —
 * the real safety net against two concurrent "Pay now" clicks racing past
 * the application-level check in InitiatePaymentHandler and starting two
 * payments (potentially with two different providers) for the same order.
 */
class m260817_140000_add_payment_pending_unique_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute(
            'CREATE UNIQUE INDEX "idx-payment-order_id-pending" ON {{%payment}} (order_id) WHERE status = \'pending\'',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP INDEX "idx-payment-order_id-pending"');
    }
}
