<?php

namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;

class HomeController extends Controller
{
    public function index(Request $request, Response $response, $args)
    {
        return $this->view->render($response, 'home.index', []);
//        return $response->withJson(['message' => 'Mock Project!']);
    }
}