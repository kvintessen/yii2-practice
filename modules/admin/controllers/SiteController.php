<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\models\LoginForm;
use app\services\AdminLoginService;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{
    // bypass the module's sidebar layout — there's nothing to navigate to
    // before logging in, and it would otherwise show a sidebar full of
    // links that just bounce back to this page
    public $layout = '@app/views/layouts/main';

    public function __construct(
        $id,
        $module,
        private readonly AdminLoginService $adminLoginService,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actionLogin(): Response|string
    {
        if (!Yii::$app->adminUser->isGuest) {
            return $this->redirect(['/admin']);
        }

        $model = new LoginForm();

        if ($model->load($this->request->post()) && $this->adminLoginService->login($model)) {
            return $this->redirect(Yii::$app->adminUser->getReturnUrl(['/admin']));
        }

        $model->password = '';

        return $this->render('login', ['model' => $model]);
    }

    public function actionLogout(): Response
    {
        Yii::$app->adminUser->logout();

        return $this->redirect(['/admin/site/login']);
    }
}
