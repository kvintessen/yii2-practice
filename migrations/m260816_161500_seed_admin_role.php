<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Creates the 'admin' RBAC role and assigns it to the seeded demo admin user (id 100).
 */
class m260816_161500_seed_admin_role extends Migration
{
    private const ADMIN_USER_ID = 100;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $authManager = Yii::$app->authManager;

        $admin = $authManager->createRole('admin');
        $admin->description = 'Full access to the admin panel.';
        $authManager->add($admin);

        $authManager->assign($admin, self::ADMIN_USER_ID);
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
