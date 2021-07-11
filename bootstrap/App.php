<?php

define('__APP_ROOT__', __DIR__ . '/../');

//$dotenv = Dotenv\Dotenv::createImmutable(__APP_ROOT__);
//$dotenv->load();

$config['settings'] = loader(__APP_ROOT__ . 'config');

$app = new \Slim\App($config);

/**
 * @var $container Slim\Container
 */
$container = $app->getContainer();

/*
 * Setup Dependencies
 */
require __DIR__ . '/dependencies.php';

/*
 * Setup Routes
 */
require __DIR__ . '/../routes/api.php';
require __DIR__ . '/../routes/web.php';

/*
 * Setup MiddleWares
 */
require __DIR__ . '/middlewares.php';