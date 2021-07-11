<?php

namespace App\Handlers;

use Exception;
use Slim\Handlers\AbstractHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class ErrorHandler extends AbstractHandler
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, Exception $exception)
    {
        $this->container->logger->addCritical(
            $exception->getCode() . ' - ' . $exception->getMessage() . "\r\n" .
            'Location: ' . $exception->getFile() . ' line: ' . $exception->getLine() . "\r\n"
        );

        return $this->container->view->render($response, 'pages.message', [
            'message' => 'Oops Sorry! we have problem! <br> problem reported to support system'
        ]);

        //default output
        return $response->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write('Something went wrong!');
    }
}