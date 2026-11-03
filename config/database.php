<?php
declare(strict_types=1);

function database(): mysqli
{
    static $connection;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $host = getenv('DB_HOST') ?: 'sql307.infinityfree.com';
    $database = getenv('DB_NAME') ?: 'if0_43249573_mvxb_db';
    $username = getenv('DB_USER') ?: 'if0_43249573';
    $password = getenv('DB_PASS') ?: 'TvKI9m9Yr4WKR';
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $connection = new mysqli($host, $username, $password, $database);
    $connection->set_charset('utf8mb4');

    return $connection;
}