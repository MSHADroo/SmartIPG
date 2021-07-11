<?php

namespace App\DataAccess;

//use App\Providers\EloquentConnection;
//use App\Providers\PdoConnection;
use Slim\Container;

class DataAccess
{
    /**
     * @var  \PDO
     */
    public $db;
    /**
     * @var  Container
     */
    public $container;

    public function __construct($container)
    {
        $this->container = $container;
//        $conn = new EloquentConnection();
//        $this->db = $conn->getConnection($this->container);
//        $conn = new PdoConnection();
//        $this->db = $conn->getConnection($this->container);
        $this->db = $container['db'];
    }

//    public function getEloquent()
//    {
//        $conn = new EloquentConnection();
//        return $this->db = $conn->getConnection($this->container);
//    }
//
//    public function getPdo()
//    {
//        $conn = new PdoConnection();
//        return $this->db = $conn->getConnection($this->container);
//    }
}
