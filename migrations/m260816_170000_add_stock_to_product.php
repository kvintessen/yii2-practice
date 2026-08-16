<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Adds stock tracking to `{{%product}}`.
 */
class m260816_170000_add_stock_to_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%product}}', 'stock', $this->integer()->notNull()->defaultValue(0)->after('price'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%product}}', 'stock');
    }
}
