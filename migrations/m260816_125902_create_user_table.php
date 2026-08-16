<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user}}`.
 */
class m260816_125902_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(255)->notNull(),
            'email' => $this->string(255)->notNull(),
            'password_hash' => $this->string(255)->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'access_token' => $this->string(64)->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-user-username', '{{%user}}', 'username', true);
        $this->createIndex('idx-user-email', '{{%user}}', 'email', true);
        $this->createIndex('idx-user-access_token', '{{%user}}', 'access_token', true);

        // Seed the same demo accounts the previous in-memory User model shipped with,
        // keeping the fixed ids/keys/tokens the existing test suite already asserts on.
        $now = time();
        $this->batchInsert(
            '{{%user}}',
            ['id', 'username', 'email', 'password_hash', 'auth_key', 'access_token', 'created_at', 'updated_at'],
            [
                [100, 'admin', 'admin@example.com', '$2y$13$gYAywKSkhfZDq9FLNdm7buKnvlRxDexf5xipSMAxQPDUxpaptmZJu', 'test100key', '100-token', $now, $now],
                [101, 'demo', 'demo@example.com', '$2y$13$alRLq1PGVMlGYwS/Y3iy3ewQns1Z8ol8Iq6Zb5k7ZwEhblA1aL29y', 'test101key', '101-token', $now, $now],
            ],
        );

        // Keep the id sequence ahead of the seeded rows so real signups don't collide with them.
        $this->execute("SELECT setval(pg_get_serial_sequence('{{%user}}', 'id'), 101)");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
