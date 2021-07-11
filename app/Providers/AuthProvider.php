<?php

namespace App\Providers;

use Pimple\{Container, ServiceProviderInterface};

class AuthProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['auth'] = static function ($container) {
            return new \App\Auth\Auth($container);
        };
    }
}
