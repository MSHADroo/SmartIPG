<?php

namespace App\Handlers;

use Slim\Handlers\AbstractHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class NotFoundHandler extends AbstractHandler
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->container->logger->addInfo(
            'not found: ' . $request->getMethod() . ' ' . $request->getUri()
        );

        $contentType = $this->determineContentType($request);
        switch ($contentType) {
            case 'application/json':
                $output = $this->renderNotFoundJson($response);
                break;
            case 'text/html':
                $output = $this->renderNotFoundHtml($response);
                break;
        }
        return $output->withStatus(404);

        //default output
        $response = new \Slim\Http\Response(404);
        return $response->write("Page not found");
    }

    protected function renderNotFoundJson($response)
    {
        return $response->withJson([
            'error' => 'Not Found'
        ]);
    }

    protected function renderNotFoundHtml($response)
    {
        return $this->container->view->render($response, 'pages.message', [
            'message' => 'Oops Sorry! we have problem! <br> problem reported to support system'
        ]);
    }
}