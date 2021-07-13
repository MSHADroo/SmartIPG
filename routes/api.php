<?php

$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        //TODO check auth middleware just on get token and get gateways
        $app->get('/gateways', App\Controllers\GatewayController::class . ':getGateways');

        $app->post('/token', App\Controllers\GatewayController::class . ':init');


    });
});

$app->get('/token/{token}', App\Controllers\GatewayController::class . ':gotobank');

$app->post('/callback/{gateway}', App\Controllers\GatewayController::class . ':callback');

