<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Handles the creation of tables `{{%order}}`, `{{%order_item}}`.
 */
class m260816_170200_create_order_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('new'),
            'total' => $this->decimal(10, 2)->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-order-user_id', '{{%order}}', 'user_id');
        $this->createIndex('idx-order-status', '{{%order}}', 'status');
        $this->addForeignKey(
            'fk-order-user_id',
            '{{%order}}',
            'user_id',
            '{{%user}}',
            'id',
        );

        // product_id/product_name/unit_price are snapshotted at order time so
        // historical orders stay accurate even if the product is later
        // renamed, repriced, or deleted.
        $this->createTable('{{%order_item}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->null(),
            'product_name' => $this->string(255)->notNull(),
            'unit_price' => $this->decimal(10, 2)->notNull(),
            'quantity' => $this->integer()->notNull(),
            'line_total' => $this->decimal(10, 2)->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-order_item-order_id', '{{%order_item}}', 'order_id');
        $this->addForeignKey(
            'fk-order_item-order_id',
            '{{%order_item}}',
            'order_id',
            '{{%order}}',
            'id',
            'CASCADE',
            'CASCADE',
        );
        $this->addForeignKey(
            'fk-order_item-product_id',
            '{{%order_item}}',
            'product_id',
            '{{%product}}',
            'id',
            'SET NULL',
            'CASCADE',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%order_item}}');
        $this->dropTable('{{%order}}');
    }
}
