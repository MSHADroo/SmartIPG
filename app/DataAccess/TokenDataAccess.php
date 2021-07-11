<?php

namespace App\DataAccess;

use PDO;

class TokenDataAccess extends DataAccess
{
    public function create($data)
    {
        $sql = 'INSERT INTO tokens 
                    (token, client_account_gateway_id, price, order_id, mobile, callback, created_at, updated_at)
                VALUES 
                    (:token, :client_account_gateway_id, :price, :order_id, :mobile, :callback, NOW(), NOW()) ';
        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':token', $data['token'], PDO::PARAM_STR);
        $stmt->bindValue(':price', $data['price'], PDO::PARAM_INT);
        $stmt->bindValue(':order_id', $data['order_id'], PDO::PARAM_INT);
        $stmt->bindValue(':mobile', $data['mobile'], PDO::PARAM_STR);
        $stmt->bindValue(':callback', $data['callback'], PDO::PARAM_STR);
        $stmt->bindValue(':client_account_gateway_id', $data['client_account_gateway_id'], PDO::PARAM_STR);
        return $stmt->execute();
    }


    public function selectByToken($data)
    {
        $sql = 'SELECT * FROM tokens 
                WHERE token = :token';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':token', $data['token'], PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function selectById($data)
    {
        $sql = 'SELECT * FROM tokens 
                WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $data['id'], PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }
     public function updateStatus($data)
    {
        $sql = 'update tokens
                set status = :status ,
                    updated_at = now()
                WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $data['status'], PDO::PARAM_STR);
        $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);
        return $stmt->execute();
    }


}
