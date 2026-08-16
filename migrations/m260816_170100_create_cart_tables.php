<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Handles the creation of tables `{{%cart}}`, `{{%cart_item}}`.
 */
class m260816_170100_create_cart_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%cart}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->null(),
            'session_id' => $this->string(64)->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // A cart belongs to at most one user or one guest session, never both
        // and never more than one row per owner — enforced as partial unique
        // indexes since either column may otherwise be null.
        $this->execute(
            'CREATE UNIQUE INDEX "idx-cart-user_id" ON {{%cart}} (user_id) WHERE user_id IS NOT NULL',
        );
        $this->execute(
            'CREATE UNIQUE INDEX "idx-cart-session_id" ON {{%cart}} (session_id) WHERE session_id IS NOT NULL',
        );
        $this->addForeignKey(
            'fk-cart-user_id',
            '{{%cart}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE',
        );

        $this->createTable('{{%cart_item}}', [
            'id' => $this->primaryKey(),
            'cart_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'quantity' => $this->integer()->notNull()->defaultValue(1),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-cart_item-cart_id-product_id', '{{%cart_item}}', ['cart_id', 'product_id'], true);
        $this->addForeignKey(
            'fk-cart_item-cart_id',
            '{{%cart_item}}',
            'cart_id',
            '{{%cart}}',
            'id',
            'CASCADE',
            'CASCADE',
        );
        $this->addForeignKey(
            'fk-cart_item-product_id',
            '{{%cart_item}}',
            'product_id',
            '{{%product}}',
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
        $this->dropTable('{{%cart_item}}');
        $this->dropTable('{{%cart}}');
    }
}
