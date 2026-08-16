<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Handles the creation of tables `{{%category}}`, `{{%product}}`, `{{%product_category}}`.
 */
class m260816_150000_create_catalog_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%category}}', [
            'id' => $this->primaryKey(),
            'parent_id' => $this->integer()->null(),
            'name' => $this->string(255)->notNull(),
            'slug' => $this->string(255)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-category-slug', '{{%category}}', 'slug', true);
        $this->createIndex('idx-category-parent_id', '{{%category}}', 'parent_id');
        $this->addForeignKey(
            'fk-category-parent_id',
            '{{%category}}',
            'parent_id',
            '{{%category}}',
            'id',
            'SET NULL',
            'CASCADE',
        );

        $this->createTable('{{%product}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'slug' => $this->string(255)->notNull(),
            'description' => $this->text()->null(),
            'price' => $this->decimal(10, 2)->notNull(),
            'sku' => $this->string(64)->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('draft'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-product-slug', '{{%product}}', 'slug', true);
        $this->createIndex('idx-product-sku', '{{%product}}', 'sku', true);

        $this->createTable('{{%product_category}}', [
            'product_id' => $this->integer()->notNull(),
            'category_id' => $this->integer()->notNull(),
        ]);

        $this->addPrimaryKey(
            'pk-product_category',
            '{{%product_category}}',
            ['product_id', 'category_id'],
        );
        $this->createIndex('idx-product_category-category_id', '{{%product_category}}', 'category_id');
        $this->addForeignKey(
            'fk-product_category-product_id',
            '{{%product_category}}',
            'product_id',
            '{{%product}}',
            'id',
            'CASCADE',
            'CASCADE',
        );
        $this->addForeignKey(
            'fk-product_category-category_id',
            '{{%product_category}}',
            'category_id',
            '{{%category}}',
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
        $this->dropTable('{{%product_category}}');
        $this->dropTable('{{%product}}');
        $this->dropTable('{{%category}}');
    }
}
