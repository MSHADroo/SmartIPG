<?php

$container->register(new App\Providers\PdoProvider());

//$container->register(new App\Providers\AuthProvider());
//
//$container->register(new App\Providers\FlashMessageProvider());
//
$container->register(new App\Providers\ViewProvider());
//
//$container->register(new App\Providers\ValidatorProvider());
//
$container->register(new App\Providers\LogServiceProvider());


/*
 * Setup Handlers
 */
//$container['errorHandler'] = function ($container) {
//    return new App\Handlers\ErrorHandler($container);
//};
//
//unset($app->getContainer()['notFoundHandler']);
//$container['notFoundHandler'] = function ($container) {
//    return new App\Handlers\NotFoundHandler($container);
//};
//
//$container['notAllowedHandler'] = function ($container) {
//    return new App\Handlers\NotAllowedHandler($container);
//};
//
//$container['phpErrorHandler'] = function ($container) {
//    return new App\Handlers\PhpErrorHandler($container);
//};