<?php

namespace Src\Database;

class Connection
{
    public static function make(): \PDO
    {
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $dbName = $_ENV['DB_NAME'];
        $userName = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];

        $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";

        return new \PDO($dsn, $userName, $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);
    }
}
