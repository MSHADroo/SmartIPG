<?php

namespace App\DataAccess;

use PDO;

class ClientAccountGatewayDataAccess extends DataAccess
{
    public function getّInfo($data)
    {
        $sql = "SELECT *
                FROM client_accounts_gateways cag 
                inner join gateways g ON cag.gateway_id = g.id
                AND cag.status = 'enable'
                AND g.status = 'enable'
                AND cag.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
}
