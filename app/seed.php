<?php

use Doctrine\DBAL\Connection;

/**
 * @var $db Connection
 */
$db = require_once __DIR__ . '/db.php';
$config = require_once __DIR__ . '/config.php';

$testRows = $config['test_db_rows'];
$db->beginTransaction();

$db->executeStatement('CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    confirmed boolean NOT NULL
)');
$db->executeStatement('CREATE TABLE IF NOT EXISTS emails (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL references users(id),
    email VARCHAR(255) NOT NULL,
    checked boolean NOT NULL,
    valid boolean NOT NULL
)');
$db->executeStatement('CREATE INDEX idx_email ON emails (email);');
$db->executeStatement('CREATE TABLE IF NOT EXISTS subscriptions (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL references users(id),
    validts INT NOT NULL,
    status VARCHAR(255) NOT NULL,
    reminder_sent_at TIMESTAMP DEFAULT NULL,
    email_sender_process boolean NOT NULL,
    subscription_reminder_process boolean NOT NULL
)');
$db->executeStatement('CREATE INDEX idx_status ON subscriptions (status);');
$db->executeStatement('CREATE INDEX idx_validts_reminder_sent_at ON subscriptions (validts, reminder_sent_at);');
$db->executeStatement('CREATE INDEX idx_validts_status ON subscriptions (validts, status);');
$db->executeStatement('CREATE TABLE IF NOT EXISTS queue_jobs (
    id SERIAL PRIMARY KEY,
    payload JSON NOT NULL,
    name VARCHAR(255) NOT NULL,
    status VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
)');
$db->executeStatement('CREATE INDEX idx_name_status ON queue_jobs (name, status);');

$db->executeQuery("INSERT INTO users(username, confirmed)
    SELECT CONCAT ('User', ROW_NUMBER () OVER ()), (round(random())::int)::boolean
    FROM generate_series(1, $testRows);
");
$db->executeQuery("INSERT INTO emails(user_id, email, checked, valid)
    SELECT ROW_NUMBER () OVER (), CONCAT (md5(random()::text), '@email.com'), (round(random())::int)::boolean, (round(random())::int)::boolean
    FROM generate_series(1, $testRows);
");
$db->executeQuery("INSERT INTO subscriptions(user_id, validts, status, reminder_sent_at, email_sender_process, subscription_reminder_process)
    SELECT ROW_NUMBER () OVER (), extract(epoch from now()), 'active', null, false, false
    FROM generate_series(1, $testRows);
");

$db->commit();

echo 'Database was seeded with ' . $config['test_db_rows'] . ' rows';