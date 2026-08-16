<?php

declare(strict_types=1);

namespace app\tests\Acceptance;

use app\tests\Support\AcceptanceTester;
use yii\helpers\Url;

final class HomeCest
{
    public function ensureThatHomePageWorks(AcceptanceTester $I)
    {
        $I->amOnPage(Url::toRoute('/site/index'));
        $I->see(\Yii::$app->name);

        $I->seeLink('Login');
        $I->click('Login');

        $I->see('Login to your account');
    }
}
