<?php

namespace App\DataAccess;

use PDO;

class LogDataAccess extends DataAccess
{
    public function create($data)
    {
        $sql = 'INSERT INTO logs 
                    (token_id, request, request_type, response, status, created_at, updated_at)
                VALUES 
                    (:token_id, :request, :request_type, :response, :status,  NOW(), NOW()) ';
        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':token_id', $data['token_id'], PDO::PARAM_INT);
        $stmt->bindValue(':request', $data['request'], PDO::PARAM_STR);
        $stmt->bindValue(':request_type', $data['request_type'], PDO::PARAM_STR);
        $stmt->bindValue(':response', json_encode($data['response']), PDO::PARAM_STR);
        $stmt->bindValue(':status', $data['status'], PDO::PARAM_STR);
        return $stmt->execute();
    }



}
