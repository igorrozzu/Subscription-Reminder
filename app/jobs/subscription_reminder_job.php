<?php

declare(strict_types=1);

use Doctrine\DBAL\Connection;
use function Func\{get_job_by_id, get_users_with_subscription_reminder_process, Jobs\SubscriptionReminder\handle};

require_once __DIR__ . '/../func/email_func.php';
require_once __DIR__ . '/../func/db_func.php';
require_once __DIR__ . '/../func/jobs/subscription_reminder_func.php';
require_once __DIR__ . '/../exceptions/exception_handler.php';
/**@var $db Connection */
$db = require_once __DIR__  . '/../db.php';

$jobId = $argv[1];
$job = get_job_by_id($db, (int) $jobId);
$userIds = json_decode($job['payload']);

$queryResult = get_users_with_subscription_reminder_process($db, $userIds);
handle($db, $queryResult, $job);
