<?php

declare(strict_types=1);

namespace app\tests\Support\Fixtures;

use app\models\User;
use yii\test\ActiveFixture;

/**
 * Loads the fixed admin(id 100)/demo(id 101) accounts from
 * tests/Support/data/user.php before a test and truncates the user table
 * again afterward, so the test database stays empty between runs instead
 * of relying on rows baked in by a migration.
 */
class UserFixture extends ActiveFixture
{
    public $modelClass = User::class;

    public $dataFile = __DIR__ . '/../data/user.php';
}
