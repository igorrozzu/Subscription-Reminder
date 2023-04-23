<?php

declare(strict_types=1);

use Doctrine\DBAL\Connection;
use function Func\{dispatch_jobs, get_users_with_valid_email};

require_once __DIR__ . '/../func/email_func.php';
require_once __DIR__ . '/../func/db_func.php';
require_once __DIR__ . '/../func/command_func.php';
require_once __DIR__ . '/../exceptions/exception_handler.php';
$config = require_once __DIR__ . '/../config.php';
/**@var $db Connection */
$db = require_once __DIR__ . '/../db.php';

$reminderLastDate = time() - $config['reminders_period_in_seconds'];
$queryResult = get_users_with_valid_email($db, $reminderLastDate);
dispatch_jobs($db, $queryResult, JOB_SUBSCRIPTION_REMINDER, $config['subscriptions_per_job'][JOB_SUBSCRIPTION_REMINDER]);

echo 'Subscription reminder command has executed successfully';
