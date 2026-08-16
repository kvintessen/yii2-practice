<?php

declare(strict_types=1);

// Fixed admin(id 100)/demo(id 101) accounts the test suite asserts against
// directly (ids, auth_key, access_token, admin/admin and demo/demo login
// credentials) — the same values the user-table migration used to seed
// before that became UserFixture's job instead.

$now = time();

return [
    'admin' => [
        'id' => 100,
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password_hash' => '$2y$13$gYAywKSkhfZDq9FLNdm7buKnvlRxDexf5xipSMAxQPDUxpaptmZJu',
        'auth_key' => 'test100key',
        'access_token' => '100-token',
        'created_at' => $now,
        'updated_at' => $now,
    ],
    'demo' => [
        'id' => 101,
        'username' => 'demo',
        'email' => 'demo@example.com',
        'password_hash' => '$2y$13$alRLq1PGVMlGYwS/Y3iy3ewQns1Z8ol8Iq6Zb5k7ZwEhblA1aL29y',
        'auth_key' => 'test101key',
        'access_token' => '101-token',
        'created_at' => $now,
        'updated_at' => $now,
    ],
];
