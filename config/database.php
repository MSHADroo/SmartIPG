<?php
return [
    'databases'=>[
        'db' => [
            'driver'    => envY(__APP_ROOT__,'DB_DRIVER', 'mysql'),
            'host'      => envY(__APP_ROOT__,'DB_HOST', 'localhost'),
            'database'  => envY(__APP_ROOT__,'DB_NAME', ''),
            'username'  => envY(__APP_ROOT__,'DB_USERNAME', ''),
            'password'  => envY(__APP_ROOT__,'DB_PASS', ''),
            'charset'   => envY(__APP_ROOT__,'DB_CHARSET', 'utf8mb4'),
            'collation' => envY(__APP_ROOT__,'DB_COLLATION', 'utf8mb4_persian_ci'),
            'prefix'    => envY(__APP_ROOT__,'DB_PREFIX', ''),
            'port'      => envY(__APP_ROOT__,'DB_PORT', 3306),
        ]
    ]
];