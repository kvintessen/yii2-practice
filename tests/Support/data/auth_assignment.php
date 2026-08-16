<?php

declare(strict_types=1);

// Assigns the 'admin' RBAC role (created by migration, permanent) to the
// fixed admin account from user.php (id 100).

return [
    'admin' => [
        'item_name' => 'admin',
        'user_id' => '100',
        'created_at' => time(),
    ],
];
