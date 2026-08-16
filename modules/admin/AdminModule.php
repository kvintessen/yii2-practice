<?php

declare(strict_types=1);

namespace app\modules\admin;

use Yii;
use yii\base\Module;
use yii\web\ForbiddenHttpException;

class AdminModule extends Module
{
    public $controllerNamespace = 'app\modules\admin\controllers';

    public $defaultRoute = 'category';

    public $layout = 'main';

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // the admin login/logout actions must be reachable without already
        // holding an admin session — everything else in this module requires one
        if ($action->controller->id === 'site' && in_array($action->id, ['login', 'logout'], true)) {
            return true;
        }

        if (Yii::$app->adminUser->isGuest) {
            Yii::$app->adminUser->loginRequired();

            return false;
        }

        if (!Yii::$app->adminUser->can('admin')) {
            throw new ForbiddenHttpException('You are not allowed to access the admin panel.');
        }

        return true;
    }
}
