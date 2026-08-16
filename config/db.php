<?php

return [
    'class' => \yii\db\Connection::class,
    // NOTE: port is always the container-internal Postgres port (5432),
    // not POSTGRES_PORT from .env — that variable only maps the host port.
    'dsn' => sprintf(
        'pgsql:host=%s;port=5432;dbname=%s',
        getenv('POSTGRES_HOST') ?: 'db',
        getenv('POSTGRES_DB') ?: 'yii2'
    ),
    'username' => getenv('POSTGRES_USER') ?: 'yii2',
    'password' => getenv('POSTGRES_PASSWORD') ?: '',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
