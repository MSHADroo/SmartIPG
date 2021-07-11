<?php

namespace App\DataAccess;

use PDO;

class AuthDataAccess extends DataAccess
{
    public function auth($data)
    {

        $sql = 'SELECT *
                FROM clients
                WHERE username = :username
                  AND password = :password';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':username', $data['username'], PDO::PARAM_STR);
        $stmt->bindValue(':password', $data['password'], PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }
}
