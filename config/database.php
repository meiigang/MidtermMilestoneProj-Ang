<?php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';

function database(): PDO
{
    static $connection;

    if (!$connection instanceof PDO) {
        $connection = (new Database())->connection();
    }

    return $connection;
}
