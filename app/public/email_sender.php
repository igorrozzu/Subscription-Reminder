<?php

declare(strict_types=1);

use Doctrine\DBAL\Connection;
use function Func\{dispatch_jobs, get_users_by_subscription_time};

require_once __DIR__ . '/../func/email_func.php';
require_once __DIR__ . '/../func/db_func.php';
require_once __DIR__ . '/../func/command_func.php';
require_once __DIR__ . '/../exceptions/exception_handler.php';
$config = require_once __DIR__ . '/../config.php';
/**@var $db Connection */
$db = require_once __DIR__ . '/../db.php';

$validDate = time() + $config['notifications_before_period_in_seconds'];
$queryResult = get_users_by_subscription_time($db, $validDate);
dispatch_jobs($db, $queryResult, JOB_EMAIL_SENDER, $config['subscriptions_per_job']['email_sender']);

echo 'Email sender command has executed successfully';