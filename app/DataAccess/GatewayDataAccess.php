<?php

namespace App\DataAccess;

use PDO;

class GatewayDataAccess extends DataAccess
{
    public function getGateways($data)
    {
        $sql = "SELECT g.*
                FROM clients s
                     INNER JOIN client_accounts_gateways sg
                        ON s.id = sg.client_id
                            AND s.status = 'enable'
                            AND sg.status = 'enable'
                            AND s.id = :client_id
                     INNER JOIN gateways g
                        ON sg.gateway_id = g.id
                            AND g.status = 'enable' ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':client_id', $data['id'], PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function gatewayInfo($data)
    {
        $sql = "SELECT *
                FROM gateways
                    WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
}
