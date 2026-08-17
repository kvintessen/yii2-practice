<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    // Test-mode shop credentials from the YooKassa merchant dashboard —
    // see README's "Оплата" section.
    'yookassaShopId' => getenv('YOOKASSA_SHOP_ID') ?: '',
    'yookassaSecretKey' => getenv('YOOKASSA_SECRET_KEY') ?: '',
    // Robokassa technical settings -> MerchantLogin + Password #1/#2. Use
    // the test password pair while ROBOKASSA_TEST_MODE=1 — see README.
    'robokassaMerchantLogin' => getenv('ROBOKASSA_MERCHANT_LOGIN') ?: '',
    'robokassaPassword1' => getenv('ROBOKASSA_PASSWORD1') ?: '',
    'robokassaPassword2' => getenv('ROBOKASSA_PASSWORD2') ?: '',
    'robokassaTestMode' => (getenv('ROBOKASSA_TEST_MODE') ?: '1') === '1',
];
