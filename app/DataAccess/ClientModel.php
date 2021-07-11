<?php

namespace App\DataAccess;

use Kernel\Abstracts\AbstractDataAccess;
use PDO;

class ClientModel extends DataAccess
{
   public $id;
   public $title;
   public $status;
}
