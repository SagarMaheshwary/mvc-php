<?php

namespace App\Core\Database;

use PDO;
use PDOException;
use App\Core\Support\Config;

class Connection
{
    
    public function make()
    {
        try{
            $name     = Config::get('database.name');
            $host     = Config::get('database.host');
            $port     = Config::get('database.port');
            $username = Config::get('database.username');
            $password = Config::get('database.password');
            $options  = Config::get('database.options');

            $pdo = new PDO(
                "mysql:host={$host};port={$port};dbname={$name}",
                $username,
                $password,
                $options
            );
            
            return $pdo;

        }catch(PDOException $e){
            $e->getMessage();
        }
    }

}
