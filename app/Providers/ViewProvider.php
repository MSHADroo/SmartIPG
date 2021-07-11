<?php

namespace App\Providers;

use Pimple\{Container, ServiceProviderInterface};

class ViewProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['view'] = function ($container) {
            return new \Slim\Views\Blade(
                $container['settings']['renderer']['blade_template_path'],
                $container['settings']['renderer']['blade_cache_path'],
                null,
                [
                    'router' => $container->get('router'),
//                    'auth' => [
//                        'user' => $container->auth->user(),
//                        'check' => $container->auth->check()
//                    ],
//                    'file' => $container->get('fileManager'),
                    'helper' => new \App\Helpers\BladeHelpers($container),
//                    'flash' => $container->flash
                ]
            );

        };
    }
}
