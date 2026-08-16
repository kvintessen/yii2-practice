<?php

declare(strict_types=1);

namespace app\tests\Unit;

use app\controllers\SiteController;
use app\models\User;
use app\services\CartMergeService;
use app\services\LoginService;
use app\services\SignupService;
use app\tests\Support\Fixtures\UserFixture;
use Yii;
use yii\base\Security;
use yii\web\IdentityInterface;
use yii\web\View;

final class LogoutTest extends \Codeception\Test\Unit
{
    public function _fixtures(): array
    {
        return ['users' => UserFixture::class];
    }

    public function testRenderLogoutLinkWhenUserIsLoggedIn(): void
    {
        $user = User::findIdentity('100');

        $controller = new SiteController(
            'site',
            Yii::$app,
            new LoginService(new Security(), new CartMergeService()),
            new SignupService(new Security(), new CartMergeService()),
        );

        $view = new View(['context' => $controller]);

        self::assertNotNull(
            $user,
            "Failed asserting that the user identity with ID '100' exists.",
        );
        self::assertInstanceOf(
            IdentityInterface::class,
            $user,
            "Failed asserting that the identity is an instance of 'Identity' class.",
        );

        Yii::$app->user->login($user);

        $html = $view->render('//layouts/main.php', ['content' => 'Hello World°']);

        self::assertStringContainsString(
            'dropdown-header">admin<',
            $html,
            'Failed asserting that the account menu shows the logged-in username.',
        );
        self::assertMatchesRegularExpression(
            '/<form[^>]+action="[^"]*site%2Flogout[^"]*"[^>]+method="post"/',
            $html,
            'Failed asserting that the logout form uses POST method.',
        );

        $controller->actionLogout();

        $html = $view->render('//layouts/main.php', ['content' => 'Hello World°']);

        self::assertStringNotContainsString(
            'dropdown-header">admin<',
            $html,
            'Failed asserting that the account menu is not rendered after logout.',
        );
    }
}
