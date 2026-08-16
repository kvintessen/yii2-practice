<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\Category;
use app\models\LoginForm;
use app\models\Product;
use app\models\SignupForm;
use app\services\LoginService;
use app\services\SignupService;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\Response;

class SiteController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly LoginService $loginService,
        private readonly SignupService $signupService,
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
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        return $this->render('index', [
            'categories' => Category::find()
                ->where(['parent_id' => null])
                ->orderBy('name')
                ->all(),
            'newArrivals' => Product::find()
                ->where(['status' => Product::STATUS_ACTIVE])
                ->orderBy(['created_at' => SORT_DESC])
                ->limit(8)
                ->all(),
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();

        if ($model->load($this->request->post()) && $this->loginService->login($model)) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', ['model' => $model]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Signup action.
     *
     * @return Response|string
     */
    public function actionSignup(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new SignupForm();

        if ($model->load($this->request->post())) {
            $user = $this->signupService->signup($model);

            if ($user !== null && Yii::$app->user->login($user)) {
                return $this->goHome();
            }
        }

        $model->password = '';
        $model->passwordRepeat = '';

        return $this->render('signup', ['model' => $model]);
    }
}
