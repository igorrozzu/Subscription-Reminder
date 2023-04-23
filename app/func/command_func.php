<?php

declare(strict_types=1);

namespace Func;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Connection;

/**
 * @param Connection $db
 * @param Result $queryResult
 * @param string $jobName
 * @param int $subscriptionsPerJob
 * @return void
 * @throws \Doctrine\DBAL\Exception
 */
function dispatch_jobs(Connection $db, Result $queryResult, string $jobName, int $subscriptionsPerJob): void
{
    $usersCount = $queryResult->rowCount();
    if (!$usersCount) {
        return;
    }

    $lastPart = $usersCount % $subscriptionsPerJob;
    $userIds = [];
    foreach ($queryResult->iterateAssociative() as $user) {
        $userIds[] = $user['id'];
        if (count($userIds) === $subscriptionsPerJob) {
            dispatch_users($db, $userIds, $jobName);
            $userIds = [];
        }
    }
    if ($lastPart !== 0) {
        dispatch_users($db, $userIds, $jobName);
    }
}

/**
 * @param Connection $db
 * @param array $userIds
 * @param string $jobName
 * @return void
 * @throws \Doctrine\DBAL\Exception
 */
function dispatch_users(Connection $db, array $userIds, string $jobName): void
{
    $db->beginTransaction();
    try {
        dispatch_job($db, $jobName, JOB_STATUS_ACTIVE, json_encode($userIds));
        if ($jobName === JOB_EMAIL_SENDER) {
            enable_email_sender_process($db, $userIds);
        } elseif ($jobName === JOB_SUBSCRIPTION_REMINDER) {
            enable_subscription_reminder_process($db, $userIds);
        }
    } catch (\Exception $e) {
        log_error($e->getMessage() . ': ' . $e->getTraceAsString());
        $db->rollBack();
    }
    $db->commit();
}
