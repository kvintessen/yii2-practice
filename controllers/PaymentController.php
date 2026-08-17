<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Order;
use app\models\Payment;
use app\services\Payment\InitiatePaymentCommand;
use app\services\Payment\InitiatePaymentHandler;
use app\services\Payment\PaymentCallbackHandler;
use app\services\Payment\PaymentExceptionInterface;
use app\services\Payment\PaymentGatewayException;
use app\services\Payment\PaymentGatewayRegistry;
use DomainException;
use Throwable;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PaymentController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly PaymentGatewayRegistry $registry,
        private readonly InitiatePaymentHandler $initiatePaymentHandler,
        private readonly PaymentCallbackHandler $callbackHandler,
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
                'only' => ['pay', 'create'],
                'rules' => [
                    [
                        'actions' => ['pay', 'create'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['post'],
                    'callback' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action): bool
    {
        if ($action->id === 'callback') {
            // Provider webhooks are server-to-server calls with no browser
            // session/cookie to carry a CSRF token — the trust boundary
            // here is each gateway's own callback verification instead
            // (see PaymentGatewayInterface::handleCallback()).
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    public function actionPay(int $orderId): string
    {
        $order = $this->findOwnedOrder($orderId);

        return $this->render('pay', [
            'order' => $order,
            'gateways' => $this->registry->all(),
        ]);
    }

    public function actionCreate(int $orderId): Response
    {
        $providerCode = (string) Yii::$app->request->post('provider');

        try {
            $payment = $this->initiatePaymentHandler->handle(new InitiatePaymentCommand(
                (int) Yii::$app->user->id,
                $orderId,
                $providerCode,
            ));
        } catch (PaymentGatewayException $e) {
            // This one carries raw provider/API failure details — log it
            // for ops, don't put it in front of the buyer.
            Yii::error(
                sprintf('Failed to initiate %s payment for order %d: %s', $providerCode, $orderId, $e),
                __METHOD__,
            );
            Yii::$app->session->setFlash(
                'error',
                'Payment could not be started. Please try again or choose a different method.',
            );

            return $this->redirect(['pay', 'orderId' => $orderId]);
        } catch (DomainException | PaymentExceptionInterface $e) {
            // Everything else here is our own message, already written to
            // be shown to the buyer (e.g. "This order is not awaiting
            // payment.", "Unknown payment provider...").
            Yii::$app->session->setFlash('error', $e->getMessage());

            return $this->redirect(['pay', 'orderId' => $orderId]);
        }

        return $this->redirect($payment->confirmation_url);
    }

    public function actionCallback(string $provider): Response
    {
        try {
            $result = $this->callbackHandler->handle($provider, Yii::$app->request);
            $ack = $this->registry->get($provider)->getCallbackAcknowledgement($result);
        } catch (PaymentExceptionInterface $e) {
            Yii::warning(sprintf('Rejected %s callback: %s', $provider, $e->getMessage()), __METHOD__);

            return $this->asPlainText('rejected', 400);
        } catch (Throwable $e) {
            Yii::error(sprintf('Failed to process %s callback: %s', $provider, $e), __METHOD__);

            return $this->asPlainText('error', 500);
        }

        return $this->asPlainText($ack);
    }

    public function actionReturn(string $provider, int $orderId): string
    {
        $order = $this->findOwnedOrder($orderId);
        $payment = Payment::find()
            ->where(['order_id' => $order->id, 'provider' => $provider])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        return $this->render('return', [
            'order' => $order,
            'payment' => $payment,
        ]);
    }

    // Deliberately not shared with OrderController::actionView(), which
    // throws 404 then 403 as two distinct steps (and so confirms whether an
    // order id exists at all, even one that isn't yours). Payment routes
    // collapse both cases into the same 404 on purpose — a payment URL
    // shouldn't be usable to probe which order ids exist.
    private function findOwnedOrder(int $orderId): Order
    {
        $order = Order::findOne($orderId);

        if ($order === null || $order->user_id !== (int) Yii::$app->user->id) {
            throw new NotFoundHttpException('The requested order does not exist.');
        }

        return $order;
    }

    private function asPlainText(string $content, int $statusCode = 200): Response
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->statusCode = $statusCode;
        $response->content = $content;

        return $response;
    }
}
