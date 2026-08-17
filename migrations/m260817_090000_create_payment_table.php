<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Handles the creation of table `{{%payment}}`.
 */
class m260817_090000_create_payment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%payment}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'provider' => $this->string(32)->notNull(),
            'external_id' => $this->string(64)->null(),
            'status' => $this->string(20)->notNull()->defaultValue('pending'),
            'amount' => $this->decimal(10, 2)->notNull(),
            'currency' => $this->string(3)->notNull()->defaultValue('RUB'),
            'confirmation_url' => $this->string(512)->null(),
            'raw_payload' => $this->text()->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-payment-order_id', '{{%payment}}', 'order_id');
        // Not unique: a payment attempt is written before the provider
        // assigns external_id, and several attempts for one order can share
        // a provider. Idempotency on callback delivery is enforced by
        // PaymentCallbackHandler, not this index.
        $this->createIndex('idx-payment-provider-external_id', '{{%payment}}', ['provider', 'external_id']);
        $this->addForeignKey(
            'fk-payment-order_id',
            '{{%payment}}',
            'order_id',
            '{{%order}}',
            'id',
            'CASCADE',
            'CASCADE',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%payment}}');
    }
}
