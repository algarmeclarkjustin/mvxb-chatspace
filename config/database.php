<?php
declare(strict_types=1);

function database(): mysqli
{
    static $connection;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $database = getenv('DB_NAME') ?: 'mvxb_db';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS') ?: '';
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $connection = new mysqli($host, $username, $password, $database);
    $connection->set_charset('utf8mb4');

    return $connection;
}