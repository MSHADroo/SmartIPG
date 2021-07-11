<?php

namespace App\DataAccess\Auth;

use App\DataAccess\DataAccess;

class AuthDataAccess extends DataAccess
{
    public function select($data)
    {
        $sql = 'select * from users 
                    where id = ? ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data['id']]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $sql = "insert into users 
                    (`role_id` , `email` , `password`, `status` , `created_at` , `updated_at`) 
                values 
                    (? , ? , ? , 'enable', NOW(), NOW() )";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$data['role_id'], $data['email'], $data['password']]);
        }catch (\PDOException $exception){
            if(strpos($exception->getMessage() , 'users_email_uindex')){
                $this->container->flash->addMessage('error', 'you are already registered with this email');
            }
            return false;
        }
    }

    public function selectByEmail($data)
    {
        $sql = 'select * from users 
                    where email = ? ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data['email']]);
        return $stmt->fetch();
    }

}