<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    // Test-mode shop credentials from the YooKassa merchant dashboard —
    // see README's "Оплата" section.
    'yookassaShopId' => getenv('YOOKASSA_SHOP_ID') ?: '',
    'yookassaSecretKey' => getenv('YOOKASSA_SECRET_KEY') ?: '',
];
