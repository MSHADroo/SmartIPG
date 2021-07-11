<?php

namespace App\Middleware;

use Exception;
use PDOException;

class ExceptionHandlerMiddleware extends Middleware
{
    public function __invoke($request , $response, $next)
    {
        try {
            $response = $next($request, $response);
        }catch (Exception $exception){
            $this->container->logger->addCritical(
                $exception->getCode() . ' - ' . $exception->getMessage() . "\r\n" .
                'Location: ' .$exception->getFile() . ' line: ' . $exception->getLine() . "\r\n"
            );

            return $this->container->view->render($response, 'pages.message',[
                'message' => 'Oops Sorry! we have problem! <br> problem reported to support system'
            ]);
        }
        return $response;
    }
}