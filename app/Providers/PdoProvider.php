<?php

namespace App\Providers;

use Pimple\{Container, ServiceProviderInterface};
use PDO;

class PdoProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['db'] = static function ($container) {
            $db = $container['settings']['databases']['db'];
            $pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['database'] . ';charset=' . $db['charset'],
                $db['username'], $db['password'], [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"]);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            return $pdo;
        };
    }
}