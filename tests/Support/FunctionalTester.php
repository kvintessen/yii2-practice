<?php

declare(strict_types=1);

namespace app\tests\Support;

/**
 * Inherited Methods
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
*/
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;

    /**
     * Logs into the separate `adminUser` component (config/test.php), the
     * admin panel's own session distinct from the storefront `user`
     * component that amLoggedInAs() targets.
     */
    public function amLoggedInAsAdmin(int $id): void
    {
        $identity = \app\models\User::findIdentity($id);
        \Yii::$app->adminUser->login($identity);
    }
}
