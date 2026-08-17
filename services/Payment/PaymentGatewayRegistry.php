<?php

declare(strict_types=1);

namespace app\services\Payment;

final class PaymentGatewayRegistry
{
    /** @var array<string, PaymentGatewayInterface> */
    private readonly array $_gatewaysByCode;

    /**
     * @param PaymentGatewayInterface[] $gateways
     */
    public function __construct(array $gateways)
    {
        $byCode = [];
        foreach ($gateways as $gateway) {
            $byCode[$gateway->getCode()->value] = $gateway;
        }

        $this->_gatewaysByCode = $byCode;
    }

    public function get(string $code): PaymentGatewayInterface
    {
        return $this->_gatewaysByCode[$code]
            ?? throw new UnknownPaymentGatewayException(sprintf('Unknown payment provider "%s".', $code));
    }

    /**
     * @return PaymentGatewayInterface[]
     */
    public function all(): array
    {
        return array_values($this->_gatewaysByCode);
    }
}
