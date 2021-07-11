<?php

namespace App\Providers;

use Pimple\{Container, ServiceProviderInterface};

class LogServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['logger'] = function ($container) {
            $logger = new \Monolog\Logger($container['settings']['logger']['name']);
            $file_handler = new \Monolog\Handler\StreamHandler($container['settings']['logger']['path']);
            $logger->pushHandler($file_handler);
            return $logger;
        };

    }
}
