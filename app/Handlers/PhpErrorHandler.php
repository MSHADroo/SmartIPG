<?php

namespace App\Handlers;

use Slim\Handlers\AbstractHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class PhpErrorHandler extends AbstractHandler
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response ,\Throwable $error)
    {
        $this->container->logger->addWarning(
            $error->getMessage()
        );

        return $this->container->view->render($response, 'pages.message',[
            'message' => 'Oops Sorry! we have problem! <br> problem reported to support system'
        ]);

        //default output
        return $response->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write('Something went wrong!');
    }
}