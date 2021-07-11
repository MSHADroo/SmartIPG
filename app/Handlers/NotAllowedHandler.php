<?php

namespace App\Handlers;

use Slim\Handlers\AbstractHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class NotAllowedHandler extends AbstractHandler
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response ,array $methods)
    {
        $this->container->logger->addDebug(
            implode(', ', $methods)
        );

        return $this->container->view->render($response, 'pages.message',[
            'message' => 'Oops Sorry! we have problem! <br> problem reported to support system'
        ]);

        //default output
        return $response->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods))
            ->withHeader('Content-type', 'text/html')
            ->write('Method must be one of: ' . implode(', ', $methods));
    }
}