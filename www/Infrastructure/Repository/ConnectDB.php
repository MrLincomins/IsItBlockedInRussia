<?php

namespace Infrastructure\Repository;

use PDO;

abstract class ConnectDB
{
    private string $host = 'mysql-server:3306';
    private string $user = 'root';
    private string $password = 'root_zapret';
    private string $database = 'zapret';
    private string $charset = "utf8mb4";
    private string $collation = 'utf8mb4_unicode_ci';
    private array $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    protected PDO $connection;


    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $dsn = "mysql:host=$this->host;dbname=$this->database;charset=$this->charset;collation=$this->collation";
        $this->connection = new PDO($dsn, $this->user, $this->password, $this->options);
    }
    //Подключение к бд
}