<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Creates the 'admin' RBAC role. Assigning it to specific users is data, not
 * schema, so it happens outside migrations — via `php yii seed/all` for
 * dev/prod, or test fixtures for the test suite.
 */
class m260816_161500_seed_admin_role extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $authManager = Yii::$app->authManager;

        $admin = $authManager->createRole('admin');
        $admin->description = 'Full access to the admin panel.';
        $authManager->add($admin);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $authManager = Yii::$app->authManager;

        $admin = $authManager->getRole('admin');

        if ($admin !== null) {
            $authManager->remove($admin);
        }
    }
}
