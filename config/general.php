<?php


return [
    'determineRouteBeforeAppMiddleware' => true,
    'addContentLengthHeader' => false,
//    'routerCacheFile' => __APP_ROOT__ . 'cache/router/routes.cache.php',
    'config_path' => __APP_ROOT__ . 'config/',
    'storage_path' => __APP_ROOT__ . 'storage/',

    'displayErrorDetails' => true, // set to false in production
    'debug' => true,

    'app' => [
        'env' =>envY(__APP_ROOT__,'ENVIRONMENT' , 'prod'),
        'logger' => [
            'name' => 'core',
            'level' => Monolog\Logger::DEBUG,
            'path' => __APP_ROOT__ . 'storage/logs/app.logs',
        ],
    ],
    'security' => [
        'token' => envY(__APP_ROOT__,'SECURE_APP_TOKEN', 'f488a28a5bae8dddb6c726951431b6a6')
    ],
    'renderer' => [
        'blade_template_path' => __APP_ROOT__ . 'resources/views', // String or array of multiple paths
        'blade_cache_path' => __APP_ROOT__ . 'storage/cache/views', // Mandatory by default, though could probably turn caching off for development
    ],
//    'redis' => [
//        'scheme'   =>  envY(__APP_ROOT__,'APP_REDIS_SCHEME', 'tcp'),
//        'host'     =>  envY(__APP_ROOT__,'APP_REDIS_HOST', 'localhost'),
//        'port'     =>  envY(__APP_ROOT__,'APP_REDIS_PORT', 6379),
//        'password'     =>  envY(__APP_ROOT__,'APP_REDIS_PASSWORD', 1234),
//    ],
];