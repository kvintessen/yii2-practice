<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Cart;
use app\models\CartItem;
use app\models\Product;
use app\services\Order\InsufficientStockException;
use app\services\Order\PlaceOrderCommand;
use app\services\Order\PlaceOrderHandler;
use DomainException;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CartController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly PlaceOrderHandler $placeOrderHandler,
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
                'only' => ['checkout', 'place-order'],
                'rules' => [
                    [
                        'actions' => ['checkout', 'place-order'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add' => ['post'],
                    'update-quantity' => ['post'],
                    'remove' => ['post'],
                    'place-order' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        return $this->render('index', [
            'cart' => Cart::findForCurrentVisitor(),
        ]);
    }

    public function actionAdd(): Response
    {
        $product = Product::find()
            ->where(['id' => (int) Yii::$app->request->post('product_id'), 'status' => Product::STATUS_ACTIVE])
            ->one();

        if ($product === null) {
            throw new NotFoundHttpException('The requested product does not exist.');
        }

        $quantity = max(1, (int) Yii::$app->request->post('quantity', 1));

        Cart::findOrCreateForCurrentVisitor()->addItem($product, $quantity);

        Yii::$app->session->setFlash('success', sprintf('Added "%s" to your cart.', $product->name));

        return $this->redirect(['index']);
    }

    public function actionUpdateQuantity(): Response
    {
        $item = $this->findOwnedItem((int) Yii::$app->request->post('item_id'));
        $quantity = (int) Yii::$app->request->post('quantity');

        if ($quantity <= 0) {
            $item->delete();
        } else {
            $item->quantity = $quantity;
            $item->save();
        }

        return $this->redirect(['index']);
    }

    public function actionRemove(): Response
    {
        $this->findOwnedItem((int) Yii::$app->request->post('item_id'))->delete();

        return $this->redirect(['index']);
    }

    public function actionCheckout(): Response|string
    {
        $cart = Cart::findForCurrentVisitor();

        if ($cart === null || $cart->items === []) {
            Yii::$app->session->setFlash('error', 'Your cart is empty.');

            return $this->redirect(['index']);
        }

        return $this->render('checkout', [
            'cart' => $cart,
        ]);
    }

    public function actionPlaceOrder(): Response
    {
        $cart = Cart::findForCurrentVisitor();

        if ($cart === null) {
            Yii::$app->session->setFlash('error', 'Your cart is empty.');

            return $this->redirect(['index']);
        }

        try {
            $order = $this->placeOrderHandler->handle(
                new PlaceOrderCommand((int) Yii::$app->user->id, $cart->id),
            );
        } catch (InsufficientStockException | DomainException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());

            return $this->redirect(['checkout']);
        }

        Yii::$app->session->setFlash('success', 'Order placed successfully.');

        return $this->redirect(['/order/view', 'id' => $order->id]);
    }

    private function findOwnedItem(int $id): CartItem
    {
        $cart = Cart::findForCurrentVisitor();
        $item = $cart !== null ? CartItem::findOne(['id' => $id, 'cart_id' => $cart->id]) : null;

        if ($item === null) {
            throw new NotFoundHttpException('The requested cart item does not exist.');
        }

        return $item;
    }
}
