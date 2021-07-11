<?php

namespace App\Controllers\Auth;

use App\Controllers\Controller;
use App\DataAccess\Auth\AuthDataAccess;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as v;

class AuthController extends Controller
{
    public function getSignIn(ServerRequestInterface $request, ResponseInterface $response)
    {
        return $this->view->render($response, 'pages.signin');
    }

    public function postSignIn(ServerRequestInterface $request, ResponseInterface $response)
    {
        $email = $request->getParam('email');
        $password = $request->getParam('password');

        if ($this->auth->attempt($email, $password)) {
            return $response->withRedirect($this->router->pathFor('home'));
        }

        return $response->withRedirect($this->router->pathFor('auth.signin'));
    }


    public function getSignUp(ServerRequestInterface $request, ResponseInterface $response)
    {
        return $this->view->render($response, 'pages.signup');
    }

    public function postSignUp(ServerRequestInterface $request, ResponseInterface $response)
    {
        $validation = $this->validator->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email(),
            'password' => v::noWhitespace()->notEmpty()
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.signup'));
        }

        $email = $request->getParam('email');
        $password = $request->getParam('password');

        $user = (new AuthDataAccess($this->container))->create([
            'role_id' => 1,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT)
        ]);

        if ($user) {
            return $response->withRedirect($this->router->pathFor('auth.signin'));
        }
        $this->flash->addMessage('error', 'there is a problem on register of your user');
        return $response->withRedirect($this->router->pathFor('auth.signup'));
    }

    public function getSignOut(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->auth->logout();
        return $response->withRedirect($this->router->pathFor('home'));
    }


}