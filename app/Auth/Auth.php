<?php

namespace App\Auth;

use App\DataAccess\Auth\AuthDataAccess;

class Auth
{
    protected $container;
    protected $dal;

    public function __construct($container)
    {
        $this->container = $container;
        $this->dal = new AuthDataAccess($container);
    }

    public function user()
    {
        if (isset($_SESSION['user'])) {
            return $this->dal->select([
                'id' => $_SESSION['user']
            ]);
        }
        return false;
    }

    public function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public function attempt($email, $password): bool
    {
        $user = $this->dal->selectByEmail([
            'email' => $email
        ]);

        if (!$user) {
            return false;
        }
        if (password_verify($password, $user->password)) {
            $_SESSION['user'] = $user->id;
            return true;
        }

        return false;
    }

    public function logout()
    {
        unset($_SESSION['user']);
    }
}