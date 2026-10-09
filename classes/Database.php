<?php
declare(strict_types=1);

class Database
{
    private PDO $connection;

    public function __construct(
        string $host = 'localhost',
        string $database = 'recipe_site',
        string $username = 'root',
        string $password = '',
        string $charset = 'utf8mb4'
    ) {
        $dsn = "mysql:host={$host};dbname={$database};charset={$charset}";
        $this->connection = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public function connection(): PDO
    {
        return $this->connection;
    }
}
