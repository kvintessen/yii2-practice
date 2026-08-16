<?php

$db = require __DIR__ . '/db.php';
// test database! Important not to run tests on production or development databases
$db['dsn'] = sprintf(
    'pgsql:host=%s;port=5432;dbname=%s',
    getenv('POSTGRES_HOST') ?: 'db',
    (getenv('POSTGRES_DB') ?: 'yii2') . '_test'
);

return $db;
