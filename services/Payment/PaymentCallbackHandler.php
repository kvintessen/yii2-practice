<?php

declare(strict_types=1);

namespace app\services\Payment;

use app\models\Order;
use app\models\Payment;
use DomainException;
use Throwable;
use Yii;
use yii\web\Request;

final class PaymentCallbackHandler
{
    private const TERMINAL_STATUSES = [
        Payment::STATUS_SUCCEEDED,
        Payment::STATUS_CANCELED,
        Payment::STATUS_FAILED,
    ];

    public function __construct(
        private readonly PaymentGatewayRegistry $registry,
    ) {
    }

    public function handle(string $providerCode, Request $request): PaymentCallbackResult
    {
        $gateway = $this->registry->get($providerCode);

        // May hit the network (e.g. YooKassa's authenticated status
        // lookup); deliberately done before opening a DB transaction.
        $result = $gateway->handleCallback($request);

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            // Locked for the rest of the transaction: two near-simultaneous
            // deliveries of the same callback (providers do retry) must not
            // both read "pending" and both act on it.
            $payment = Payment::findBySql(
                'SELECT * FROM {{%payment}} WHERE provider = :provider AND external_id = :external_id FOR UPDATE',
                [':provider' => $providerCode, ':external_id' => $result->externalId],
            )->one();

            if ($payment === null) {
                Yii::warning(
                    sprintf('Callback for unknown payment %s/%s.', $providerCode, $result->externalId),
                    __METHOD__,
                );
                $transaction->commit();

                // The signature/verification still checked out, so the
                // caller must still acknowledge it the way this provider
                // expects — otherwise it'll retry forever for a payment
                // we'll never recognize.
                return $result;
            }

            if (in_array($payment->status, self::TERMINAL_STATUSES, true)) {
                // Already settled — redelivered callbacks are a no-op, but
                // still get acknowledged (see the unknown-payment branch
                // above for why).
                $transaction->commit();

                return $result;
            }

            $payment->status = match ($result->status) {
                PaymentStatus::Succeeded => Payment::STATUS_SUCCEEDED,
                PaymentStatus::Canceled => Payment::STATUS_CANCELED,
                PaymentStatus::Failed => Payment::STATUS_FAILED,
                PaymentStatus::Pending => Payment::STATUS_PENDING,
            };
            $payment->raw_payload = $result->rawPayload;

            if (!$payment->save()) {
                throw new DomainException('Unable to update the payment.');
            }

            if ($result->status === PaymentStatus::Succeeded) {
                // Guarded by status=NEW so a duplicate concurrent callback
                // (already handled above via TERMINAL_STATUSES, but belt
                // and suspenders under real concurrency) can't double-apply.
                Order::updateAll(
                    ['status' => Order::STATUS_PAID],
                    ['id' => $payment->order_id, 'status' => Order::STATUS_NEW],
                );
            }

            $transaction->commit();

            return $result;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }
    }
}
