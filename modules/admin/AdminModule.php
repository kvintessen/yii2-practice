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

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (Yii::$app->user->isGuest) {
            Yii::$app->user->loginRequired();

            return false;
        }

        if (!Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('You are not allowed to access the admin panel.');
        }

        return true;
    }
}
