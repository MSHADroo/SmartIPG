<?php

namespace App\Providers;

use Pimple\{Container, ServiceProviderInterface};

class FlashMessageProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['flash'] = function () {
            return new \Slim\Flash\Messages();
        };
    }
}
