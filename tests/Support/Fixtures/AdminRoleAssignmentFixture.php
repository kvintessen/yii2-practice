<?php

declare(strict_types=1);

namespace app\tests\Support\Fixtures;

use yii\test\ActiveFixture;

/**
 * Assigns the 'admin' RBAC role to the fixed admin account (id 100) from
 * UserFixture. The 'admin' auth_item itself is schema, created by
 * migration, and stays permanent — only the assignment is test data.
 *
 * Depends on UserFixture being loaded in the same `_fixtures()` set so
 * user id 100 exists; the role item existing is guaranteed by migration.
 */
class AdminRoleAssignmentFixture extends ActiveFixture
{
    public $tableName = '{{%auth_assignment}}';

    public $dataFile = __DIR__ . '/../data/auth_assignment.php';
}
