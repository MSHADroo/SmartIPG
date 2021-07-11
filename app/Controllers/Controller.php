<?php

namespace App\Controllers;

use App\DataAccess\LogDataAccess;
use Slim\Container;

class Controller
{
    public $container;

    public function __construct(Container $container)
    {
        $this->container = $container;

    }

    public function __get($property)
    {
        if($this->container->{$property}){
            return $this->container->{$property};
        }
    }

    public function transactionLog($data)
    {   $LogDataAccess = new LogDataAccess($this->container);
         return $LogDataAccess->create([
            'token_id'     => $data['token_id'],
            'request'      => is_array($data['request']) ? json_encode($data['request']) : $data['request'],
            'request_type' => $data['request_type'],
            'response'     => is_array($data['response']) ? json_encode($data['response']) : $data['response'],
            'status'       => $data['status'],
        ]);
    }
}