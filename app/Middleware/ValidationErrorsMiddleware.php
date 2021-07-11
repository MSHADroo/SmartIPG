<?php

namespace App\Middleware;

class ValidationErrorsMiddleware extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        if (array_key_exists('errors', $_SESSION)) {
            $this->container->view->set('errors', $_SESSION['errors']);
            unset($_SESSION['errors']);
        } else {
            $this->container->view->set('errors', []);
        }
        $response = $next($request, $response);
        return $response;
    }
}