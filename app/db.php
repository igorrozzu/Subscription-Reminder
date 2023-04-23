<?php
require_once __DIR__.'/vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

$dbParams = [
    'dbname' => getenv('POSTGRES_DB'),
    'user' => getenv('POSTGRES_USER'),
    'password' => getenv('POSTGRES_PASSWORD'),
    'host' => getenv('POSTGRES_HOST'),
    'driver' => 'pdo_pgsql',
];

return DriverManager::getConnection($dbParams);