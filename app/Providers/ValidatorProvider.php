<?php

namespace App\Providers;

use Pimple\{Container, ServiceProviderInterface};

class ValidatorProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['validator'] = static function () {
            return new \App\Validation\Validator();
        };

    }
}
